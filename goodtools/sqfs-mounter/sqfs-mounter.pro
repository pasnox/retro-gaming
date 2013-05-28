# Add more folders to ship with the application, here
folder_01.source = qml
folder_01.target = .
folder_02.source = resources
folder_02.target = .

DEPLOYMENTFOLDERS = folder_01 folder_02

linux-open-pandora-g++ {
    sh_script.source = desktop/sqfs-mounter.sh
    sh_script.target = bin

    DEPLOYMENTFOLDERS *= sh_script

    # workaround for QtCreator to allow absolutefile path to be deployed
    ABSOLUTE_FILE_PATH_WORKAROUND = ../../../../../../../..

    QT_LIBS_TO_DEPLOY = \
        libQtDeclarative \
        libQtScript \
        libQtCore \
        libQtSvg \
        libQtGui \
        libQtSql \
        libQtXmlPatterns \
        libQtNetwork \
        libQtDBus \
        libQtXml \
        libQtOpenGL

    LIBS_TO_DEPLOY = \
        $${CROSS_DEVICE_TOOLCHAIN_SYSROOT_PATH}/lib/libstdc++.so \
        $${CROSS_DEVICE_TOOLCHAIN_SYSROOT_PATH}/lib/libstdc++.so.6 \
        $${CROSS_DEVICE_TOOLCHAIN_SYSROOT_PATH}/lib/libstdc++.so.6.0.17 \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libmount.so.1.1.0 \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libmount.so.1 \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libmount.so \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libxcb.so.1.1.0 \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libxcb.so.1 \
        $${CROSS_DEVICE_OPTWARES_PATH}/lib/libxcb.so

    isEqual( QT_MAJOR_VERSION, 5 ) {
        QT_LIBS_TO_DEPLOY *= libQtWidgets
        QT_PLUGINS_TO_DEPLOY *= platforms
    }

    for( lib, QT_LIBS_TO_DEPLOY ) {
        isEqual( QT_MAJOR_VERSION, 5 ) {
            lib = $$replace( lib, Qt, Qt5 )
        }

        # *.so.x.y.z
        eval( qt_mmp_$${lib}.source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${CROSS_DEVICE_SDK_PATH}/opt/Qt/$${QT_VERSION}/lib/$${lib}.so.$${QT_VERSION} )
        eval( qt_mmp_$${lib}.target = bin )
        # *.so.x.y
        eval( qt_mm_$${lib}.source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${CROSS_DEVICE_SDK_PATH}/opt/Qt/$${QT_VERSION}/lib/$${lib}.so.$${QT_MAJOR_VERSION}.$${QT_MINOR_VERSION} )
        eval( qt_mm_$${lib}.target = bin )
        # *.so.x
        eval( qt_m_$${lib}.source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${CROSS_DEVICE_SDK_PATH}/opt/Qt/$${QT_VERSION}/lib/$${lib}.so.$${QT_MAJOR_VERSION} )
        eval( qt_m_$${lib}.target = bin )
        # *.so
        eval( qt_$${lib}.source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${CROSS_DEVICE_SDK_PATH}/opt/Qt/$${QT_VERSION}/lib/$${lib}.so )
        eval( qt_$${lib}.target = bin )
        DEPLOYMENTFOLDERS *= qt_mmp_$${lib} qt_mm_$${lib} qt_m_$${lib} qt_$${lib}
    }

    for( plugin, QT_PLUGINS_TO_DEPLOY ) {
        eval( qt_plugin_$${plugin}.source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${CROSS_DEVICE_SDK_PATH}/opt/Qt/$${QT_VERSION}/plugins/$${plugin} )
        eval( qt_plugin_$${plugin}.target = bin )
        DEPLOYMENTFOLDERS *= qt_plugin_$${plugin}
    }

    for( lib, LIBS_TO_DEPLOY ) {
        eval( lib_$$basename( lib ).source = $${ABSOLUTE_FILE_PATH_WORKAROUND}/$${lib} )
        eval( lib_$$basename( lib ).target = bin )
        DEPLOYMENTFOLDERS *= lib_$$basename( lib )
    }
}

# Additional import path used to resolve QML modules in Creator's code model
QML_IMPORT_PATH =

# If your application uses the Qt Mobility libraries, uncomment the following
# lines and add the respective components to the MOBILITY variable.
# CONFIG += mobility
# MOBILITY +=

# Speed up launching on MeeGo/Harmattan when using applauncherd daemon
# CONFIG += qdeclarative-boostable

include( src/3rdparty/diskmanager-qt.pri )

QT *= opengl

DEPENDPATH *= . \
    src

HEADERS *= \
    src/Settings.h \
    src/Model.h \
    src/ApplicationViewer.h

SOURCES *= src/main.cpp \
    src/Settings.cpp \
    src/Model.cpp \
    src/ApplicationViewer.cpp

# Please do not modify the following two lines. Required for deployment.
include( qmlapplicationviewer/qmlapplicationviewer.pri )
qtcAddDeployment()
