import QtQuick 1.1

Rectangle {
    id: rectangle1
    width: 800
    height: 480

    FSView {
        anchors.bottomMargin: 0
        anchors.top: parent.top
        anchors.right: parent.right
        anchors.bottom: bottomPanel.top
        anchors.left: parent.left
        anchors.topMargin: 0
    }

    Rectangle {
        id: bottomPanel
        x: 0
        y: 373
        width: 799
        height: 100
        color: "#00000000"
        radius: 5
        anchors.rightMargin: 1
        anchors.bottomMargin: 1
        border.color: "#000000"
        anchors.right: parent.right
        anchors.bottom: parent.bottom
        anchors.left: parent.left

        Flow {
            id: buttonsFlow
            anchors.fill: parent
            anchors.margins: 4
            spacing: 10

            function buttonWidth() {
                var count = 3
                return ( width -( ( count -1 ) *spacing ) ) /count
            }

            Button {
                id: bImagesPath
                width: buttonsFlow.buttonWidth()
                height: 90
                pointSize: 30
                bold: true
                text: "Images Path"
                icon: ""

                onClicked: {
                    dataSettings.requestUserImagesPath()
                }
            }

            Button {
                id: bAskPassProgram
                width: buttonsFlow.buttonWidth()
                height: 90
                pointSize: 30
                bold: true
                text: "AskPass Program"
                icon: ""

                onClicked: {
                    dataSettings.requestAskPassProgram()
                }
            }

            Button {
                id: bQuit
                width: buttonsFlow.buttonWidth()
                height: 90
                text: "Quit"
                pointSize: 30
                bold: true
                icon: ""

                onClicked: {
                    Qt.quit()
                }
            }
        }
    }
}
