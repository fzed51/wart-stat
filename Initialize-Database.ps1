param(
    [string]$DatabasePath = ".\data\wart_stat.db",
    [switch]$Force = $false,
    [switch]$SeedData = $false
)

Write-Host "War Thunder Reports Database Initializer" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$sqlitePath = ".\sqlite-tools\sqlite3.exe"

if (-not (Test-Path $sqlitePath)) {
    Write-Host "SQLite3 not found at: $sqlitePath" -ForegroundColor Red
    Write-Host "Please download SQLite from https://www.sqlite.org/download.html and place it in the sqlite-tools directory." -ForegroundColor Red
    exit 1
}

Write-Host "SQLite found: $sqlitePath" -ForegroundColor Green
$sqliteCmd = $sqlitePath

$dbPath = $DatabasePath
$schemaPath = ".\data\schema.sql"
$seedPath = ".\data\seed.sql"

if (-not (Test-Path $schemaPath)) {
    Write-Host "Schema file not found: $schemaPath" -ForegroundColor Red
    exit 1
}

if (Test-Path $dbPath) {
    if ($Force) {
        Remove-Item $dbPath -Force
        Write-Host "Removed existing database" -ForegroundColor Yellow
    }
    else {
        $backup = "$($dbPath).backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
        Copy-Item $dbPath $backup
        Write-Host "Backed up database to: $backup" -ForegroundColor Yellow
    }
}

$dataDir = Split-Path $dbPath -Parent
if (-not (Test-Path $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir -Force | Out-Null
}

Write-Host "Initializing database..." -ForegroundColor Yellow
$schemaContent = Get-Content $schemaPath -Raw
$schemaContent | & $sqliteCmd $dbPath

if ($LASTEXITCODE -eq 0) {
    Write-Host "Database initialized successfully!" -ForegroundColor Green
}
else {
    Write-Host "Failed to initialize database" -ForegroundColor Red
    exit 1
}

if ($SeedData -and (Test-Path $seedPath)) {
    Write-Host "Seeding lookup tables..." -ForegroundColor Yellow
    $seedContent = Get-Content $seedPath -Raw
    $seedContent | & $sqliteCmd $dbPath
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Lookup tables seeded!" -ForegroundColor Green
    }
}

Write-Host "Database path: $dbPath" -ForegroundColor Cyan
Write-Host "Complete!" -ForegroundColor Green
