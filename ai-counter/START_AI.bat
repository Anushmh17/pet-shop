@echo off
TITLE Pet Shop AI Backend
echo --- STARTING PET SHOP AI ENGINE ---
echo -----------------------------------

:: 1. Force current folder
cd /d "%~dp0"

:: 2. Check for Python
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] 'python' command not found. Trying 'py'...
    py --version >nul 2>&1
    if %errorlevel% neq 0 (
        echo [FATAL ERROR] Python was not found in your system PATH.
        echo Please ensure Python is installed AND "Add Python to PATH" was checked.
        echo If you JUST installed it, please RESTART YOUR PC.
        pause
        exit /b
    )
    set PY_CMD=py
) else (
    set PY_CMD=python
)

:: 3. Setup Virtual Environment
if not exist "venv" (
    echo [STEP 1/3] Creating virtual environment...
    %PY_CMD% -m venv venv
    if %errorlevel% neq 0 (
        echo [ERROR] Failed to create venv!
        pause
        exit /b
    )
)

:: 4. Install requirements
echo [STEP 2/3] Installing/Checking requirements (this can take 2-3 mins)...
call venv\Scripts\activate
pip install -r requirements.txt --quiet
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install requirements!
    pause
    exit /b
)

:: 5. Start Server
echo [STEP 3/3] Launching AI engine on port 8000...
echo -----------------------------------
echo SUCCESS! KEEP THIS WINDOW OPEN.
echo -----------------------------------
%PY_CMD% -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Server crashed.
    pause
)
pause
