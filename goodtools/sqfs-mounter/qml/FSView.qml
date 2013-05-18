import QtQuick 1.1
import com.socute.cppcomponents 1.0

Rectangle {
    id: root
    width: 336
    height: 300
    clip: true

    GridView {
        id: view
        anchors.bottomMargin: 0
        anchors.leftMargin: 0
        anchors.topMargin: 0
        anchors.rightMargin: 0
        anchors.fill: parent
        cellWidth: width /4
        cellHeight: 114
        model: dataModel
        delegate: FSItem {
            width: view.cellWidth
            height: view.cellHeight

            onClicked: {
                if ( customIsMounted ) {
                    dataModel.umount( customBackingFile );
                }
                else {
                    dataModel.mount( customBackingFile );
                }
            }
        }
    }
}
