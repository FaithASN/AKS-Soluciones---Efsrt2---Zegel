param([int]$Puerto = 8001)
$ErrorActionPreference = 'Stop'
$aksProject = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $aksProject
$aksPhpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($aksPhpCommand) { $aksPhp = $aksPhpCommand.Source }
elseif (Test-Path -LiteralPath 'C:/xampp/php/php.exe') { $aksPhp = 'C:/xampp/php/php.exe' }
else { throw 'Instala PHP 8.2 o superior y agrégalo al PATH.' }
if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) { throw 'Faltan dependencias. Ejecuta composer install en esta carpeta.' }
if (-not (Test-Path -LiteralPath '.env')) { throw 'Copia .env.example a .env y configura MySQL como indica README.md.' }
Write-Host "Abre http://127.0.0.1:$Puerto. Detén con Ctrl+C. MySQL debe estar encendido."
& $aksPhp artisan serve --host=127.0.0.1 --port=$Puerto
