@echo off
echo 'link in start'
set /p PATHREPOS=Please enter the directory you want it to be link to:
set oldpath=%cd%
cd %PATHREPOS%
call modman init
call modman link %oldpath%
cd %oldpath%.
echo 'link in finish'
Pause