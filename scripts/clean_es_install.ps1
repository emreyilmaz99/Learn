<#
  clean_es_install.ps1
  Usage: Run from project root (C:\Users\gamep\OneDrive\Masaüstü\Laravel\Learn) in an elevated PowerShell.
    .\scripts\clean_es_install.ps1 -Force

  What it does:
  - Stops and removes kibana and elasticsearch containers if present
  - Runs `docker-compose down -v` to remove containers and named volumes from the compose file
  - Attempts to remove common ES volumes if they exist
  - Starts docker-compose up -d
  - Streams Elasticsearch logs briefly and attempts to create an enrollment token

  IMPORTANT: This will delete Elasticsearch data volumes. You confirmed data is disposable.
#>

param(
    [switch]$Force
)

function Run-Checked {
    param($cmd)
    Write-Host "==> $cmd" -ForegroundColor Cyan
    iex $cmd
}

if (-not $Force) {
    $confirm = Read-Host "This script will DELETE Elasticsearch volumes (data). Type YES to continue"
    if ($confirm -ne 'YES') {
        Write-Host "Aborted by user." -ForegroundColor Yellow
        exit 1
    }
}

Push-Location -LiteralPath (Split-Path -Path $MyInvocation.MyCommand.Definition -Parent) | Out-Null
Set-Location ..\

Write-Host "Project root: $(Get-Location)" -ForegroundColor Green

# 1) Stop and remove containers if present
Run-Checked "docker rm -f kibana elasticsearch 2>$null || echo 'containers removed or not present'"

# 2) Down the compose file and remove volumes referenced by it
Run-Checked "docker-compose -f .\docker-compose.yml down -v"

# 3) Try to remove commonly used ES volumes if they exist
$volumesToTry = @('learn_esdata','elasticsearch-data','esdata')
foreach ($v in $volumesToTry) {
    $exists = docker volume ls --format '{{.Name}}' | Select-String -SimpleMatch $v
    if ($exists) {
        Run-Checked "docker volume rm $v || echo 'failed to remove $v or already removed'"
    }
}

# 4) Start the stack
Run-Checked "docker-compose -f .\docker-compose.yml up -d"

# 5) Wait for Elasticsearch to initialize and stream logs
Write-Host "Waiting a few seconds and then streaming elasticsearch logs (watch for keystore/cert generation)..." -ForegroundColor Green
Start-Sleep -Seconds 5
Run-Checked "docker logs -f elasticsearch --tail 200"

# 6) Try to create enrollment token
Write-Host "Attempting to create Kibana enrollment token..." -ForegroundColor Green
try {
    $token = docker exec -it elasticsearch /bin/sh -c "bin/elasticsearch-create-enrollment-token -s kibana" 2>&1 | Out-String
    if ($token -match 'eyJ') {
        Write-Host "Enrollment token (paste into Kibana):" -ForegroundColor Green
        Write-Host $token
    } else {
        Write-Host "Enrollment token creation did not return expected token. Output:" -ForegroundColor Yellow
        Write-Host $token
    }
} catch {
    Write-Host "Token creation command failed:" -ForegroundColor Red
    Write-Host $_.Exception.Message
}

Pop-Location

Write-Host "Script finished. If token was not produced, paste the last lines of 'docker logs elasticsearch --tail 200' here and I'll help further." -ForegroundColor Cyan
