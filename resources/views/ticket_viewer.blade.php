<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Ticket viewer</title>
  {!! $blade_vars_JS !!}
  @vite('resources/css/style.css')
	@vite('resources/vue/TicketViewer/app.css')
</head>
<body>
	<div id="app"></div>
	@vite('resources/vue/TicketViewer/app.js')
</body>
</html>
