import QtQuick 1.1

Rectangle {
    id: root
    width: 60
    height: 29
    color: "#91a473"
    radius: 5
    border.color: "#000000"
    clip: true

    property alias text: text.text
    property alias bold: text.font.bold
    property alias capitalization: text.font.capitalization
    property alias family: text.font.family
    property alias italic: text.font.italic
    property alias letterSpacing: text.font.letterSpacing
    property alias pixelSize: text.font.pixelSize
    property alias pointSize: text.font.pointSize
    property alias strikeout: text.font.strikeout
    property alias underline: text.font.underline
    property alias weight: text.font.weight
    property alias wordSpacing: text.font.wordSpacing
    property alias icon: icon.source
    property bool checkable: false
    property bool checked: false

    signal clicked()
    signal pressed()
    signal released()

    MouseArea {
        id: mouseArea
        anchors.fill: parent

        Text {
            id: text
            text: qsTr("text")
            anchors.topMargin: 3
            anchors.bottomMargin: 2
            anchors.rightMargin: 3
            wrapMode: Text.WordWrap
            font.pointSize: 9
            verticalAlignment: Text.AlignVCenter
            horizontalAlignment: Text.AlignHCenter
            anchors.left: icon.right
            anchors.right: parent.right
            anchors.bottom: parent.bottom
            anchors.top: parent.top
            anchors.leftMargin: 3
        }

        Image {
            id: icon
            width: height
            anchors.top: parent.top
            anchors.topMargin: 3
            anchors.bottom: parent.bottom
            anchors.bottomMargin: 2
            anchors.left: parent.left
            anchors.leftMargin: 3
            fillMode: Image.PreserveAspectFit
            source: "../resources/48x48/consoles/SegaSaturn-48-1.png"
        }

        onClicked: {
            if ( checkable ) {
                root.checked = !root.checked
            }

            root.clicked()
        }

        onPressed: {
            root.updateState()
            root.pressed()
        }

        onReleased: {
            root.updateState()
            root.released()
        }
    }

    function updateState() {
        if ( checkable ) {
            if ( mouseArea.pressed ) {
                state = mouseArea.pressed ? 'Checked' : ''
            }
            else {
                state = checked ? 'Checked' : ''
            }
        }
        else {
            state = mouseArea.pressed ? 'Checked' : ''
        }
    }

    onCheckableChanged: {
        updateState()
    }

    onCheckedChanged: {
        updateState()
    }

    states: [
        State {
            name: "Checked"
            PropertyChanges {
                target: root
                color: "#6e7c4f"
            }

            PropertyChanges {
                target: text
                anchors.topMargin: 5
                anchors.leftMargin: 3
                anchors.bottomMargin: 2
                anchors.rightMargin: 3
                scale: 1
            }

            PropertyChanges {
                target: icon
                anchors.leftMargin: 4
                anchors.topMargin: 4
                anchors.bottomMargin: 1
            }
        }
    ]
}
