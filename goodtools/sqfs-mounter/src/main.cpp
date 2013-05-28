#include <QApplication>

#include "ApplicationViewer.h"

Q_DECL_EXPORT int main( int argc, char* argv[] )
{
#if QT_VERSION < 0x050000
    QApplication::setGraphicsSystem( "raster" );
#endif

    QScopedPointer<QApplication> app( createApplication( argc, argv ) );
    app->setApplicationName( "Squash File System Mounter" );
    app->setApplicationVersion( "1.0.0" );
    app->setOrganizationDomain( "SoCute.org" );
    app->setOrganizationName( "So' Cute" );

    ApplicationViewer viewer;
    viewer.deviceShow();

    return app->exec();
}
