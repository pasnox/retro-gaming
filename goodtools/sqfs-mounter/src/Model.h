#ifndef MODEL_H
#define MODEL_H

#include <QAbstractListModel>
#include <QStringList>

#include "DiskManagerFactory.h"
#include "Disk.h"

class Settings;
class DiskManager;
class QFileSystemWatcher;

class Model : public QAbstractListModel
{
    Q_OBJECT
    Q_ENUMS( CustomRole )

public:
    enum CustomRole {
        DecorationCustomRole = Qt::UserRole,
        DecorationUrlCustomRole,
        DisplayCustomRole,
        DeviceNameCustomRole,
        BackingFileCustomRole,
        IsMountedCustomRole,
        MountPointsCustomRole,
        ToolTipCustomRole
    };

    Model( Settings* settings = 0, QObject* parent = 0 );

    DiskManager* diskManager() const;

    virtual bool insertRows( int row, int count, const QModelIndex& parent = QModelIndex() );
    virtual bool removeRows( int row, int count, const QModelIndex& parent = QModelIndex() );

    virtual int rowCount( const QModelIndex& parent = QModelIndex() ) const;
    virtual QVariant data( const QModelIndex& index, int role = Qt::DisplayRole ) const;

#if QT_VERSION >= 0x050000
    virtual QHash<int, QByteArray> roleNames() const;
#endif

    Q_INVOKABLE QVariant customData( int row, Model::CustomRole customRole ) const;
    Q_INVOKABLE bool mount( const QString& backingFile );
    Q_INVOKABLE bool umount( const QString& backingFile );

private:
    typedef QMap<QString, QString> MapStringString;
    typedef MapStringString ImageDeviceMapping;
    typedef MapStringString DeviceImageMapping;

private:
    Settings* mSettings;
    DiskManager* mManager;
    QFileSystemWatcher* mWatcher;
    ImageDeviceMapping mItems;
    DeviceImageMapping mItemsMapping;
    mutable QMap<QString, QString> mDisplayRoleCache;

    QModelIndex imageModelIndex( const QString& backingFile ) const;
    QString modelIndexImage( int row ) const;
    QString modelIndexDevice( int row ) const;
    QString decorationFilePath( const QString& key ) const;

private slots:
    void imagesPathChanged();
};

#endif // MODEL_H
