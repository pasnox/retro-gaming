import QtQuick 1.1

Rectangle {
    id: root
    width: 300
    height: 114
    clip: true
    color: "transparent"
    border.color: "transparent"
    state: getState()

    property alias title: title.text;
    property alias icon: icon.source;

    signal clicked()
    signal pressed()
    signal released()

    Rectangle {
        id: item
        anchors.rightMargin: 2
        anchors.leftMargin: 1
        anchors.bottomMargin: 2
        anchors.topMargin: 1
        anchors.fill: parent
        border.width: 1
        border.color: "#000000"
        radius: 5
        color: "#e1e1ea"

        MouseArea {
            id: mouseArea
            anchors.fill: parent

            Item {
                id: container
                anchors.topMargin: 3
                anchors.rightMargin: 3
                anchors.leftMargin: 3
                anchors.bottomMargin: 3
                anchors.fill: parent

                Text {
                    id: title
                    y: 0
                    height: 48
                    text: customDisplay
                    anchors.left: icon.right
                    anchors.leftMargin: 3
                    anchors.right: parent.right
                    anchors.rightMargin: 0
                    anchors.bottom: parent.bottom
                    anchors.bottomMargin: 0
                    wrapMode: Text.WordWrap
                    font.pointSize: 7
                    clip: true
                    font.bold: true
                    verticalAlignment: Text.AlignVCenter
                    horizontalAlignment: Text.AlignHCenter
                    anchors.top: parent.top
                    anchors.topMargin: 0
                }

                Image {
                    id: icon
                    width: 96
                    anchors.left: parent.left
                    anchors.leftMargin: 0
                    source: customDecorationUrl
                    sourceSize.height: 96
                    sourceSize.width: 96
                    fillMode: Image.PreserveAspectFit
                    anchors.top: parent.top
                    anchors.bottom: parent.bottom
                    anchors.topMargin: 0
                }
            }

            onClicked: {
                root.clicked()
            }

            onPressed: {
                root.pressed()
            }

            onReleased: {
                root.released()
            }
        }
    }

    function getState() {
        if ( mouseArea.pressed ) {
            return "Pressed"
        }
        else {
            return customIsMounted ? "Mounted" : ""
        }
    }

    states: [
        State {
            name: "Mounted"
            PropertyChanges {
                target: item
                color: "#afafe6"
                border.color: "#1603aa"
            }
        },
        State {
            name: "Pressed"
            PropertyChanges {
                target: item
                color: "#757dd1"
                border.color: "#27068d"
            }
        }
    ]
}
