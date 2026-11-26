<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('titulo','Panel') · AKS Soluciones</title><link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap.min.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/sistema.css') }}"><script src="{{ asset('assets/js/sistema.js') }}" defer></script>@stack('head')</head><body class="management"><a class="skip-link" href="#contenido">Saltar al contenido</a>
<aside class="sidebar"><a class="brand" href="{{ route('panel') }}"><span class="brand-symbol">◢</span><span><strong>AKS</strong><small>Soluciones</small></span></a><nav aria-label="Módulos del sistema">
@php($links=['panel'=>['Panel','▦'],'pedidos.index'=>['Pedidos','▤']])
@if(auth()->user()->es('ADMINISTRADOR','RECEPCIONISTA')) @php($links['clientes.index']=['Clientes','♧']) @endif
@if(auth()->user()->es('ADMINISTRADOR')) @php($links+=['cotizaciones.index'=>['Cotizaciones','▧'],'catalogo.index'=>['Servicios','◇'],'reportes.index'=>['Reportes','▥'],'usuarios.index'=>['Usuarios','♙']]) @endif
@foreach($links as $ruta=>[$texto,$icono])<a href="{{ route($ruta) }}" class="{{ request()->routeIs(str_replace('.index','.*',$ruta))?'active':'' }}"><span aria-hidden="true">{{ $icono }}</span>{{ $texto }}</a>@endforeach
<a href="{{ route('inicio') }}">Web pública</a></nav><div class="profile-summary"><span class="avatar">{{ mb_substr(auth()->user()->nombre,0,1) }}</span><div><strong>{{ auth()->user()->nombre }}</strong><small>{{ auth()->user()->perfil->nombre }}</small></div></div></aside>
<div class="workspace"><header class="workspace-header"><span>Sistema de gestión de servicios técnicos</span><form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Cerrar sesión</button></form></header>
<main id="contenido" class="workspace-main"><div class="page-heading"><div><span class="eyebrow">AKS SOLUCIONES</span><h1>@yield('titulo','Panel')</h1><p>@yield('subtitulo')</p></div><div>@yield('acciones')</div></div>
@if(session('ok'))<div class="alert alert-success" role="status">{{ session('ok') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Revisa los datos ingresados.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('contenido')
</main><footer class="workspace-footer">AKS Soluciones · Vista · Negocio · Datos</footer></div>@stack('scripts')</body></html>
