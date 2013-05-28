#include "ApplicationViewer.h"
#include "Settings.h"
#include "Model.h"

#include <QDesktopWidget>
#include <QtDeclarative>

#ifdef QT_OPENGL_LIB
#include <QGLWidget>
#endif

ApplicationViewer::ApplicationViewer( QWidget* parent )
    : QmlApplicationViewer( parent ),
      mSettings( new Settings( this ) ),
      mModel( new Model( mSettings ) )
{
    if ( isPandora() ) {
        setWindowFlags( Qt::Window | Qt::CustomizeWindowHint | Qt::FramelessWindowHint | Qt::X11BypassWindowManagerHint );
    }

    qmlRegisterType<Settings>( "com.socute.cppcomponents", 1, 0, "Settings" );
    qmlRegisterType<Model>( "com.socute.cppcomponents", 1, 0, "Model" );

    rootContext()->setContextProperty( "dataSettings", mSettings );
    rootContext()->setContextProperty( "dataModel", mModel );

    setFocusPolicy( Qt::StrongFocus );
    setOptimizationFlag( QGraphicsView::DontAdjustForAntialiasing );
    setOptimizationFlag( QGraphicsView::DontSavePainterState );
    setResizeMode( QDeclarativeView::SizeRootObjectToView );

#ifdef QT_OPENGL_LIB
    //setViewport( new QGLWidget() );
#endif
    viewport()->setAttribute( Qt::WA_OpaquePaintEvent );
    viewport()->setAttribute( Qt::WA_NoSystemBackground );
    viewport()->setAttribute( Qt::WA_PaintUnclipped );
    viewport()->setAttribute( Qt::WA_TranslucentBackground, false );

    setOrientation( QmlApplicationViewer::ScreenOrientationLockLandscape );
    setMainQmlFile( QLatin1String( "qml/main.qml" ) );
}

bool ApplicationViewer::isPandora() const {
    return QApplication::desktop()->screenGeometry( this ).size() == QSize( 800, 480 );
}

void ApplicationViewer::deviceShow() {
    if ( isPandora() ) {
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
