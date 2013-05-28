#ifndef APPLICATIONVIEWER_H
#define APPLICATIONVIEWER_H

#include "qmlapplicationviewer.h"

class Settings;
class Model;

class ApplicationViewer : public QmlApplicationViewer
{
    Q_OBJECT

public:
    ApplicationViewer( QWidget* parent = 0 );

    bool isPandora() const;

public slots:
    void deviceShow();

private:
    Settings* mSettings;
    Model* mModel;
};

#endif // APPLICATIONVIEWER_H
