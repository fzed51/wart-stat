$config = Get-Content -Path "$PSScriptRoot\url-wt.json" | ConvertFrom-Json
$url = $config.url
Start-Process $url
