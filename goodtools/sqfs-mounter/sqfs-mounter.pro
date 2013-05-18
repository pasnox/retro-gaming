# Add more folders to ship with the application, here
folder_01.source = qml
folder_01.target = .
folder_02.source = resources
folder_02.target = .
DEPLOYMENTFOLDERS = folder_01 folder_02

# Additional import path used to resolve QML modules in Creator's code model
QML_IMPORT_PATH =

# If your application uses the Qt Mobility libraries, uncomment the following
# lines and add the respective components to the MOBILITY variable.
# CONFIG += mobility
# MOBILITY +=

# Speed up launching on MeeGo/Harmattan when using applauncherd daemon
# CONFIG += qdeclarative-boostable

include( src/3rdparty/diskmanager-qt.pri )

DEPENDPATH *= . \
    src

HEADERS *= \
    src/Settings.h \
    src/Model.h

SOURCES *= src/main.cpp \
    src/Settings.cpp \
    src/Model.cpp

# Please do not modify the following two lines. Required for deployment.
include( qmlapplicationviewer/qmlapplicationviewer.pri )
qtcAddDeployment()
