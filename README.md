retro-gaming
============

This repository is dedicated to somes helper tools related to retro gaming / emulators.

goodtools
=========

This folder contains goodtools.php script which is an helper for mass collections scan / update and squashfs images creator.
See goodtools.php --help for usage.
It also contains mount-sqfs.php and umount-sqfs-php scripts which can easily mount / umount sqfs files created with goodtools.php.
See --help parameter for usage.

TIPS
====

Some commands require root privileges to run commands ( mount / umount ).
You can edit your sudoers file to allow mount / umount call not requiring passwords.
Edit this way:

`sudo visudo`

In the text editor, near to the end add:

`%sudo ALL= NOPASSWD: /bin/mount`

`%sudo ALL= NOPASSWD: /bin/umount`

Feel free to adds others commands for you if needed.
