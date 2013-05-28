#include "Settings.h"
#include "ApplicationViewer.h"

#if QT_VERSION < 0x050000
#include <QDesktopServices>
#else
#include <QStandardPaths>
#endif

#include <QDir>
#include <QApplication>
#include <QInputDialog>
#include <QProcess>
#include <QFileDialog>
#include <QDebug>

#if defined( HAS_UDEV_DISK )
#include <libmount/libmount.h>
#endif

#define KEY_IMAGES_PATH "Images/File Path"
#define KEY_DISK_MANAGER_TYPE "Disk Manager/Type"
#define KEY_MOUNT_PASSWORD "Mount/Password"
#define KEY_ASK_PASS_PROGRAM "Mount/Ask Pass Program"

class MainWindowHandler {
public:
    MainWindowHandler() {
        _window = qobject_cast<ApplicationViewer*>( QApplication::activeWindow() );

        if ( _window ) {
            //_window->showNormal();
        }
    }

    ~MainWindowHandler() {
        if ( _window ) {
            //_window->deviceShow();
        }
    }

    ApplicationViewer* window() const {
        return _window;
    }

private:
    ApplicationViewer* _window;
};

Settings::Settings( QObject* parent )
    : QSettings( Settings::filePath(), QSettings::IniFormat, parent )
{
}

QString Settings::imagesPath() const
{
    return Settings::realPath( value( KEY_IMAGES_PATH, "/media/Emulators/Roms/GoodTools" ).toString() );
}

void Settings::setImagesPath( const QString& directoryPath )
{
    setValue( KEY_IMAGES_PATH, Settings::realPath( directoryPath ) );
    emit imagesPathChanged();
}

QString Settings::mountPassword() const
{
    return value( KEY_MOUNT_PASSWORD ).toString();
}

void Settings::setMountPassword( const QString& password )
{
    setValue( KEY_MOUNT_PASSWORD, password );
}

QString Settings::askPassProgram() const
{
    return value( KEY_ASK_PASS_PROGRAM ).toString();
}

void Settings::setAskPassProgram( const QString& program )
{
    setValue( KEY_ASK_PASS_PROGRAM, program );
}

DiskManagerFactory::Type Settings::diskManagerType() const
{
    return DiskManagerFactory::Type( value( KEY_DISK_MANAGER_TYPE, DiskManagerFactory::UDev ).toInt() );
}

void Settings::setDiskManagerType( DiskManagerFactory::Type type )
{
    setValue( KEY_DISK_MANAGER_TYPE, type );
}

bool Settings::mount( const QString& backingFile )
{
    QString askPass = askPassProgram();

    if ( askPass.isEmpty() ) {
        requestAskPassProgram();
        askPass = askPassProgram();
    }

    if ( askPass.isEmpty() ) {
        return false;
    }

    /*bool ok;
    const QString password = QInputDialog::getText( 0, tr( "Mount file..." ), tr( "Please enter the admin password:" ), QLineEdit::PasswordEchoOnEdit, mountPassword(), &ok );

    if ( !ok || password.isEmpty() ) {
        return false;
    }

    setMountPassword( password );*/

    MainWindowHandler handler;
    QStringList arguments;

    if ( askPass.endsWith( "kdesudo", Qt::CaseInsensitive ) ) {
        arguments << "--attach";
        arguments << QString::number( handler.window()->winId() );
    }

    arguments << QString( "mount -o loop,ro '%1' '%2'" ).arg( backingFile ).arg( Settings::mountPoint( backingFile ) );

    return QProcess::startDetached( askPass, arguments );
}

bool Settings::umount( const QString& backingFile )
{
    QString askPass = askPassProgram();

    if ( askPass.isEmpty() ) {
        requestAskPassProgram();
        askPass = askPassProgram();
    }

    if ( askPass.isEmpty() ) {
        return false;
    }

    /*bool ok;
    const QString password = QInputDialog::getText( 0, tr( "Unmount file..." ), tr( "Please enter the admin password:" ), QLineEdit::PasswordEchoOnEdit, mountPassword(), &ok );

    if ( !ok || password.isEmpty() ) {
        return false;
    }

    setMountPassword( password );*/

    MainWindowHandler handler;
    QStringList arguments;

    if ( askPass.endsWith( "kdesudo", Qt::CaseInsensitive ) ) {
        arguments << "--attach";
        arguments << QString::number( handler.window()->winId() );
    }

    arguments << QString( "umount '%1'" ).arg( Settings::mountPoint( backingFile ) );

    return QProcess::startDetached( askPass, arguments );
}

void Settings::requestUserImagesPath()
{
    MainWindowHandler handler;
    const QString filePath = QFileDialog::getExistingDirectory( handler.window(), tr( "Choose the path of your *.sqfs files" ), imagesPath() );

    if ( filePath.isNull() ) {
        return;
    }

    setImagesPath( filePath );
}

void Settings::requestAskPassProgram()
{
    MainWindowHandler handler;
    const QString program = QFileDialog::getOpenFileName( handler.window(), tr( "Choose the askpass program" ), askPassProgram() );

    if ( program.isNull() ) {
        return;
    }

    setAskPassProgram( program );
}

QString Settings::realPath( const QString& filePath )
{
    const QFileInfo fi( filePath );
    return filePath.trimmed().isEmpty() ? QString::null : QDir::cleanPath( QFileInfo( fi.isSymLink() ? fi.symLinkTarget() : fi.absoluteFilePath() ).absoluteFilePath() );
}

QString Settings::filePath()
{
    return QDir::cleanPath(
#if QT_VERSION < 0x050000
        QDesktopServices::storageLocation( QDesktopServices::DataLocation )
#else
        QStandardPaths::writableLocation( QStandardPaths::DataLocation )
#endif
            .append( "/settings.ini" )
    );
}

QString Settings::mountPoint( const QString& backingFile )
{
    const QString path = QString( "%1/sqfs/%2" ).arg( QDir::tempPath() ).arg( QFileInfo( backingFile ).baseName() );

    if ( !QFile::exists( path ) ) {
        QDir().mkpath( path );
    }

    return path;
}

#if defined( HAS_UDEV_DISK )
bool Settings::pmount( const QString& backingFile )
{
    int result = -1;
    libmnt_context* context = mnt_new_context();

    if ( !context ) {
        qWarning( "%s: 0", Q_FUNC_INFO );
        goto done;
    }

    if ( ( result = mnt_context_set_fstype( context, "squashfs" ) ) != 0 ) {
        qWarning( "%s: 1:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_mflags( context, MS_RDONLY ) ) != 0 ) {
        qWarning( "%s: 2:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_user_mflags( context, MNT_MS_LOOP ) ) != 0 ) {
        qWarning( "%s: 3:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_source( context, backingFile.toUtf8().constData() ) ) != 0 ) {
        qWarning( "%s: 4:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_target( context, Settings::mountPoint( backingFile ).toUtf8().constData() ) ) != 0 ) {
        qWarning( "%s: 5:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_mount( context ) ) != 0 ) {
        qWarning( "%s: 7:%i", Q_FUNC_INFO, result );
        goto done;
    }

done:
    mnt_free_context( context );

    qWarning() << Q_FUNC_INFO << backingFile << result;
    return result == 0;
}

bool Settings::pumount( const QString& backingFile )
{
    int result = -1;
    libmnt_context* context = mnt_new_context();

    if ( !context ) {
        qWarning( "%s: 0", Q_FUNC_INFO );
        goto done;
    }

    /*if ( ( result = mnt_context_set_fstype( context, "squashfs" ) ) != 0 ) {
        qWarning( "%s: 1:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_mflags( context, MS_RDONLY ) ) != 0 ) {
        qWarning( "%s: 2:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_user_mflags( context, MNT_MS_LOOP ) ) != 0 ) {
        qWarning( "%s: 3:%i", Q_FUNC_INFO, result );
        goto done;
    }*/

    if ( ( result = mnt_context_set_source( context, backingFile.toUtf8().constData() ) ) != 0 ) {
        qWarning( "%s: 4:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_set_target( context, Settings::mountPoint( backingFile ).toUtf8().constData() ) ) != 0 ) {
        qWarning( "%s: 5:%i", Q_FUNC_INFO, result );
        goto done;
    }

    if ( ( result = mnt_context_umount( context ) ) != 0 ) {
        qWarning( "%s: 7:%i", Q_FUNC_INFO, result );
        goto done;
    }

done:
    mnt_free_context( context );

    qWarning() << Q_FUNC_INFO << backingFile << result;
    return result == 0;
}
#endif
