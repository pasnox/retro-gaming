#include <QApplication>
#include <QDesktopWidget>
#include <QtDeclarative>
#include <QDebug>

#include "qmlapplicationviewer.h"
#include "Settings.h"
#include "Model.h"

#include <QListView>
#include "DiskModel.h"
#include "DiskProxyModel.h"
#include "DiskManager.h"

class Viewer : public QmlApplicationViewer {
    Q_OBJECT

public:
    Viewer( QWidget* parent = 0 )
        : QmlApplicationViewer( parent )
    {
#ifdef ENABLE_OPENGL
        setViewport( new QGLWidget() );
#endif
        viewport()->setAttribute( Qt::WA_OpaquePaintEvent );
        viewport()->setAttribute( Qt::WA_NoSystemBackground );
        viewport()->setAttribute( Qt::WA_PaintUnclipped );
        viewport()->setAttribute( Qt::WA_TranslucentBackground, false );

        setFocusPolicy( Qt::StrongFocus );
        setOptimizationFlag( QGraphicsView::DontAdjustForAntialiasing );
        setOptimizationFlag( QGraphicsView::DontSavePainterState );
        setResizeMode( QDeclarativeView::SizeRootObjectToView );
    }

    bool isPandora() const {
        return QApplication::desktop()->screenGeometry( this ).size() == QSize( 800, 480 );
    }

    void deviceShow() {
        if ( isPandora() ) {
            setWindowFlags( Qt::CustomizeWindowHint | Qt::FramelessWindowHint );
            showMaximized();
        }
        else {
            showExpanded();

            if ( !isMaximized() && !isFullScreen() ) {
                QRect rect( frameGeometry() );
                rect.moveCenter( QApplication::desktop()->screenGeometry( this ).center() );
                move( rect.topLeft() );
            }
        }
    }
};

Q_DECL_EXPORT int main( int argc, char* argv[] )
{
    QApplication::setGraphicsSystem( "raster" );

    QScopedPointer<QApplication> app( createApplication( argc, argv ) );
    app->setApplicationName( "Squash File System Mounter" );
    app->setApplicationVersion( "1.0.0" );
    //app->setOrganizationDomain( "" );
    app->setOrganizationName( "So' Cute" );

    qmlRegisterType<Settings>( "com.socute.cppcomponents", 1, 0, "Settings" );
    qmlRegisterType<Model>( "com.socute.cppcomponents", 1, 0, "Model" );

    Settings s;
    Model m( &s );

    Viewer viewer;
    viewer.setOrientation( QmlApplicationViewer::ScreenOrientationAuto );
    viewer.rootContext()->setContextProperty( "dataSettings", &s );
    viewer.rootContext()->setContextProperty( "dataModel", &m );
    viewer.setMainQmlFile( QLatin1String( "qml/main.qml" ) );
    viewer.deviceShow();

    /*QListView* v = new QListView;
    v->setViewMode( QListView::IconMode );
    v->setResizeMode( QListView::Adjust );
    v->setModel( &m );

    QListView* lv = new QListView;
    DiskModel* dm = new DiskModel( m.diskManager(), lv );
    DiskProxyModel* dpm = new DiskProxyModel( DiskProxyModel::PropertyTypeIn, lv );
    dpm->setPropertyTypeFilter( Disk::Name, QRegExp( ".*\/loop[\\d]+" ) );
    dpm->setSourceModel( dm );
    lv->setModel( dpm );

    QPushButton* b = new QPushButton( "Debug" );
    QObject::connect( b, SIGNAL( clicked() ), m.diskManager(), SLOT( debug() ) );

    QSplitter w;
    w.addWidget( &viewer );
    w.addWidget( v );
    w.addWidget( lv );
    w.addWidget( b );
    w.resize( 800, 300 );
    w.show();*/

    return app->exec();
}

#include "main.moc"
