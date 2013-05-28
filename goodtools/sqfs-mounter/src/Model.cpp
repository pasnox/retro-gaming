#include "Model.h"
#include "DiskManagerFactory.h"
#include "DiskManager.h"
#include "Settings.h"

#include <QDir>
#include <QIcon>
#include <QFont>
#include <QApplication>
#include <QFileSystemWatcher>
#include <QDebug>

Model::Model( Settings* settings, QObject* parent )
    : QAbstractListModel( parent ),
      mSettings( settings ),
      mManager( DiskManagerFactory::create( settings ? settings->diskManagerType() : DiskManagerFactory::Invalid, this ) ),
      mWatcher( new QFileSystemWatcher( this ) )
{
    Q_ASSERT( mManager );
    Q_ASSERT( mSettings );

#if QT_VERSION < 0x050000
    QHash<int, QByteArray> names = roleNames();

    names[ Model::DecorationCustomRole ] = "customDecoration";
    names[ Model::DecorationUrlCustomRole ] = "customDecorationUrl";
    names[ Model::DisplayCustomRole ] = "customDisplay";
    names[ Model::DeviceNameCustomRole ] = "customDeviceName";
    names[ Model::BackingFileCustomRole ] = "customBackingFile";
    names[ Model::IsMountedCustomRole ] = "customIsMounted";
    names[ Model::MountPointsCustomRole ] = "customMountPoints";
    names[ Model::ToolTipCustomRole ] = "customToolTip";

    setRoleNames( names );
#endif

    connect( mManager, SIGNAL( diskAdded( const QString& ) ), this, SLOT( imagesPathChanged() ) );
    connect( mManager, SIGNAL( diskChanged( const QString& ) ), this, SLOT( imagesPathChanged() ) );
    connect( mManager, SIGNAL( diskRemoved( const QString& ) ), this, SLOT( imagesPathChanged() ) );
    connect( mSettings, SIGNAL( imagesPathChanged() ), this, SLOT( imagesPathChanged() ) );
    connect( mWatcher, SIGNAL( directoryChanged( const QString& ) ), this, SLOT( imagesPathChanged() ) );

    imagesPathChanged();
}

DiskManager* Model::diskManager() const
{
    return mManager;
}

bool Model::insertRows(int row, int count, const QModelIndex &parent)
{
    //qWarning() << Q_FUNC_INFO << row << count << parent;
    return QAbstractListModel::insertRows( row, count, parent );
}

bool Model::removeRows(int row, int count, const QModelIndex &parent)
{
    //qWarning() << Q_FUNC_INFO << row << count << parent;
    return QAbstractListModel::removeRows( row, count, parent );
}

int Model::rowCount( const QModelIndex& parent ) const
{
    return parent == QModelIndex() ? mItems.count() : 0;
}

QVariant Model::data( const QModelIndex& index, int role ) const
{
    if ( !index.isValid() ) {
        return QVariant();
    }

    switch ( role ) {
        case Qt::DecorationRole:
            return customData( index.row(), Model::DecorationCustomRole );

        case Qt::DisplayRole:
            return customData( index.row(), Model::DisplayCustomRole );

        case Qt::ToolTipRole:
            return customData( index.row(), Model::ToolTipCustomRole );

        case Qt::FontRole: {
            QFont font;
            font.setBold( customData( index.row(), Model::IsMountedCustomRole ).toBool() );
            return font;
        }

        case Model::DecorationCustomRole:
        case Model::DecorationUrlCustomRole:
        case Model::DisplayCustomRole:
        case Model::DeviceNameCustomRole:
        case Model::BackingFileCustomRole:
        case Model::IsMountedCustomRole:
        case Model::MountPointsCustomRole:
        case Model::ToolTipCustomRole:
            return customData( index.row(), Model::CustomRole( role ) );
    }

    return QVariant();
}

#if QT_VERSION >= 0x050000
QHash<int, QByteArray> Model::roleNames() const
{
    QHash<int, QByteArray> names = QAbstractListModel::roleNames();

    names[ Model::DecorationCustomRole ] = "customDecoration";
    names[ Model::DecorationUrlCustomRole ] = "customDecorationUrl";
    names[ Model::DisplayCustomRole ] = "customDisplay";
    names[ Model::DeviceNameCustomRole ] = "customDeviceName";
    names[ Model::BackingFileCustomRole ] = "customBackingFile";
    names[ Model::IsMountedCustomRole ] = "customIsMounted";
    names[ Model::MountPointsCustomRole ] = "customMountPoints";
    names[ Model::ToolTipCustomRole ] = "customToolTip";

    return names;
}
#endif

QVariant Model::customData( int row, Model::CustomRole customRole ) const
{
    if ( row < 0 || row >= rowCount() ) {
        return QVariant();
    }

    switch ( customRole ) {
        case Model::DecorationCustomRole: {
            return QIcon( customData( row, Model::DecorationUrlCustomRole ).toString() );
        }

        case Model::DecorationUrlCustomRole: {
            const QString key = QFileInfo( modelIndexImage( row ) ).baseName().section( "_", 0, 0 );
            const QString fn = decorationFilePath( key );

            if ( !QFile::exists( fn ) ) {
                qWarning() << key << fn;
            }

            return fn;
        }

        case Model::DisplayCustomRole: {
            const QString baseName = QFileInfo( modelIndexImage( row ) ).baseName();
            QString& text = mDisplayRoleCache[ baseName ];

            if ( text.isEmpty() ) {
                text = QString( baseName ).replace( "-", "_" );
                text = QString( "%1\n(%2, %3)" )
                    .arg( text.section( "_", 0, 0 ) )
                    .arg( text.section( "_", 1, 1 ) )
                    .arg( text.section( "_", 2, 2 ) )
                ;
            }

            return text;
        }

        case Model::DeviceNameCustomRole: {
            return modelIndexDevice( row );
        }

        case Model::BackingFileCustomRole: {
            return modelIndexImage( row );
        }

        case Model::IsMountedCustomRole: {
            const QString deviceName = modelIndexDevice( row );
            const Disk disk = mManager->disk( deviceName );
            return disk.isValid() ? disk.property( Disk::IsMounted ) : false;
        }

        case Model::MountPointsCustomRole: {
            const QString deviceName = modelIndexDevice( row );
            const Disk disk = mManager->disk( deviceName );
            return disk.isValid() ? disk.property( Disk::MountPaths ) : QVariant();
        }

        case Model::ToolTipCustomRole: {
            QStringList strings;
            strings << tr( "Display: %1" ).arg( customData( row, Model::DisplayCustomRole ).toString() );
            strings << tr( "Device: %1" ).arg( customData( row, Model::DeviceNameCustomRole ).toString() );
            strings << tr( "Is Mounted: %1" ).arg( customData( row, Model::IsMountedCustomRole ).toString() );
            strings << tr( "Backing File: %1" ).arg( customData( row, Model::BackingFileCustomRole ).toString() );
            strings << tr( "Mount Points: %1" ).arg( customData( row, Model::MountPointsCustomRole ).toStringList().join( ", " ) );
            return strings.join( "\n" );
        }
    }

    return QVariant();
}

bool Model::mount( const QString& backingFile )
{
    return mSettings->mount( backingFile );
}

bool Model::umount( const QString& backingFile )
{
    return mSettings->umount( backingFile );
}

QModelIndex Model::imageModelIndex( const QString& backingFile ) const
{
    const int row = mItems.keys().indexOf( backingFile );
    return row != -1 ? createIndex( row, 0 ) : QModelIndex();
}

QString Model::modelIndexImage( int row ) const
{
    if ( row < 0 || row >= mItems.count() ) {
        return QString::null;
    }

    const ImageDeviceMapping::const_iterator it = mItems.constBegin() +row;
    return it.key();
}

QString Model::modelIndexDevice( int row ) const
{
    if ( row < 0 || row >= mItems.count() ) {
        return QString::null;
    }

    const ImageDeviceMapping::const_iterator it = mItems.constBegin() +row;
    return it.value();
}

QString Model::decorationFilePath( const QString& key ) const
{
    QString path = QApplication::applicationDirPath();

    if ( path.endsWith( "/" ) ) {
        path.chop( 1 );
    }

    const bool inBin = path.endsWith( "/bin" );

#if defined( Q_OS_MACX )
    const QString fix = "/";
#else
    const QString fix = inBin ? "/../" : "/";
#endif
    return QDir::cleanPath( QString( "%1/%2resources/48x48/goodtools/%3.png" ).arg( path ).arg( fix ).arg( key ) );
}

void Model::imagesPathChanged()
{
    //qWarning() << Q_FUNC_INFO;
    const bool locked = mWatcher->blockSignals( true );
    const QStringList paths = mWatcher->directories() << mWatcher->files();

    if ( !paths.isEmpty() ) {
        mWatcher->removePaths( paths );
    }

    mWatcher->addPath( mSettings->imagesPath() );
    mWatcher->blockSignals( locked );

    const QFileInfoList files = QDir( mSettings->imagesPath() ).entryInfoList( QStringList( "*.sqfs" ), QDir::Files );
    ImageDeviceMapping items;
    DeviceImageMapping itemsMapping;

    foreach ( const Disk& disk, mManager->disksList() ) {
        const QString deviceName = disk.property( Disk::Name ).toString();
        const QString backingFile = Settings::realPath( disk.property( Disk::BackingFile ).toString() );

        if ( !backingFile.isEmpty() ) {
            items[ backingFile ] = deviceName;
            itemsMapping[ deviceName ] = backingFile;
        }
    }

    foreach ( const QFileInfo& file, files ) {
        const QString backingFile = Settings::realPath( file.absoluteFilePath() );
        items[ backingFile ];
    }

    const QList<QString> oldKeys = mItems.keys();
    const QList<QString> newKeys = items.keys();
    QMap<QString, int> oldRows;
    QMap<QString, int> newRows;

    for ( int i = 0; i < oldKeys.count(); i++ ) {
        oldRows[ oldKeys[ i ] ] = i;
    }

    for ( int i = 0; i < newKeys.count(); i++ ) {
        newRows[ newKeys[ i ] ] = i;
    }

    //emit layoutAboutToBeChanged();
    beginResetModel();

    const QModelIndexList oldIndexes = persistentIndexList();
    QModelIndexList newIndexes;

    foreach ( const QModelIndex& index, oldIndexes ) {
        const QString key = oldKeys[ index.row() ];
        const int newRow = newRows.value( key, -1 );
        newIndexes << ( newRow >= 0 ? createIndex( newRow, index.column() ) : QModelIndex() );
    }

    mItems = items;
    mItemsMapping = itemsMapping;
    changePersistentIndexList( oldIndexes, newIndexes );

    //emit layoutChanged();
    endResetModel();
}
