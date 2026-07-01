@echo off
cd /d "%~dp0.."
"C:\nvm4w\nodejs\npm.cmd" run dev -- --host 127.0.0.1 --port 5174
