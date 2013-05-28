DISKMANAGER_QT = $${PWD}/diskmanager-qt/src

CONFIG *= udisks
CONFIG *= udisks2
CONFIG *= udev_disk

CONFIG( udisks ) {
    DEFINES *= HAS_UDISKS_DISK
    linux*:QT *= dbus
}

CONFIG( udisks2 ) {
    DEFINES *= HAS_UDISKS2_DISK
    linux*:QT *= dbus
}

CONFIG( udev_disk ) {
    DEFINES *= HAS_UDEV_DISK
    linux*:LIBS *= -ludev -lmount
    linux-open-pandora-g++ {
        INCLUDEPATH *= $${CROSS_DEVICE_OPTWARES_PATH}/include
        LIBS *= -L$${CROSS_DEVICE_OPTWARES_PATH}/lib -lblkid
    }
}

DEPENDPATH *= \
    $${DISKMANAGER_QT} \
    $${DISKMANAGER_QT}/udisks \
    $${DISKMANAGER_QT}/udisks2 \
    $${DISKMANAGER_QT}/udev

INCLUDEPATH *= \
    $${DISKMANAGER_QT}

HEADERS *= \
    $${DISKMANAGER_QT}/Disk.h \
    $${DISKMANAGER_QT}/DiskManager.h \
    $${DISKMANAGER_QT}/DiskManagerFactory.h \
    $${DISKMANAGER_QT}/DiskModel.h \
    $${DISKMANAGER_QT}/DiskProxyModel.h

SOURCES *= \
    $${DISKMANAGER_QT}/Disk.cpp \
    $${DISKMANAGER_QT}/DiskManager.cpp \
    $${DISKMANAGER_QT}/DiskManagerFactory.cpp \
    $${DISKMANAGER_QT}/DiskModel.cpp \
    $${DISKMANAGER_QT}/DiskProxyModel.cpp

CONFIG( udisks ) {
    HEADERS *= $${DISKMANAGER_QT}/udisks/UDisks_p.h \
        $${DISKMANAGER_QT}/udisks/UDisks.h \
        $${DISKMANAGER_QT}/udisks/UDisksManager.h

    SOURCES *= $${DISKMANAGER_QT}/udisks/UDisks.cpp \
        $${DISKMANAGER_QT}/udisks/UDisksManager.cpp
}

CONFIG( udisks2 ) {
    HEADERS *= $${DISKMANAGER_QT}/udisks2/UDisks2_p.h \
        $${DISKMANAGER_QT}/udisks2/UDisks2.h \
        $${DISKMANAGER_QT}/udisks2/UDisks2Manager.h

    SOURCES *= $${DISKMANAGER_QT}/udisks2/UDisks2.cpp \
        $${DISKMANAGER_QT}/udisks2/UDisks2Manager.cpp
}

CONFIG( udev_disk ) {
    HEADERS *= $${DISKMANAGER_QT}/udev/UDev.h \
        $${DISKMANAGER_QT}/udev/UDevManager.h

    SOURCES *= $${DISKMANAGER_QT}/udev/UDev.cpp \
        $${DISKMANAGER_QT}/udev/UDevManager.cpp
}
