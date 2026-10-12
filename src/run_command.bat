@echo off
set PROJECT_ROOT=%~dp0..
pushd %PROJECT_ROOT%
%*
popd
