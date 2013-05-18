#ifndef SETTINGS_H
#define SETTINGS_H

#include <QSettings>
#include <QStringList>

#include "DiskManagerFactory.h"

class Settings : public QSettings
{
    Q_OBJECT

public:
    Settings( QObject* parent = 0 );

    Q_INVOKABLE QString imagesPath() const;
    Q_INVOKABLE void setImagesPath( const QString& directoryPath );

    Q_INVOKABLE QString mountPassword() const;
    Q_INVOKABLE void setMountPassword( const QString& password );

    Q_INVOKABLE QString askPassProgram() const;
    Q_INVOKABLE void setAskPassProgram( const QString& program );

    Q_INVOKABLE DiskManagerFactory::Type diskManagerType() const;
    Q_INVOKABLE void setDiskManagerType( DiskManagerFactory::Type type );

    Q_INVOKABLE bool mount( const QString& backingFile );
    Q_INVOKABLE bool umount( const QString& backingFile );

    Q_INVOKABLE void requestUserImagesPath();
    Q_INVOKABLE void requestAskPassProgram();

    Q_INVOKABLE static QString realPath( const QString& filePath );
    Q_INVOKABLE static QString filePath();
    Q_INVOKABLE static QString mountPoint( const QString& backingFile );

#if defined( HAS_UDEV_DISK )
    Q_INVOKABLE static bool pmount( const QString& backingFile );
    Q_INVOKABLE static bool pumount( const QString& backingFile );
#endif

signals:
    void imagesPathChanged();
};

#endif // SETTINGS_H
