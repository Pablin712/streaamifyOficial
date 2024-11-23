<html> 
<head> 
<title>@yield('title')</title> 
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1, shrink
to-fit=no">
 <!-- Bootstrap CSS --> 
<link rel="stylesheet" 
href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
 > 
</head> 
<body> 
<div class="container"> 
<nav> 
<ul> 
<li><a href="/home">Home</a></li> 
<li><a href="/portafolio">Portafolio</a></li> 
<li><a href="/acerca">Acerca de</a></li> 
<li><a href="/contacto">Contacto</a></li> 
</ul> 
</nav> 
@yield('content') 
</div> 
</body> 
</html>