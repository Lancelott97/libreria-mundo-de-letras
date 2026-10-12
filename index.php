
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Mundo de Letras</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <!-- Encabezado -->
    <header class="encabezado text-center py-4">
        <h1>📚 Mundo de Letras</h1>
        <p class="mb-0">Descubre nuevas historias y encuentra tu próximo libro favorito.</p>
    </header>

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Librería Mundo de Letras
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="carrito.php">🛒 Mi carrito</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="registro_usuario.php">Registrarse</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Iniciar sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <main class="container my-5">

        <!-- Presentación -->
        <section id="inicio" class="presentacion p-5 rounded mb-5">
            <h2>Bienvenido a Mundo de Letras</h2>
            <p>
                Explora nuestro catálogo de libros, descubre nuevos autores
                y disfruta de una experiencia de compra sencilla y segura.
            </p>
            <a href="#catalogo" class="btn btn-primary">
                Explorar catálogo
            </a>
        </section>

        <!-- Catálogo -->
        <section id="catalogo" class="mb-5">
            <h2 class="titulo-seccion">Nuestro catálogo</h2>

            <div class="row g-4">

                <div class="col-12 col-sm-6 col-lg-4">
                    <article class="card h-100 tarjeta-libro">
                        <img
                            src="https://placehold.co/400x260/ddd6fe/312e81?text=Novela"
                            class="card-img-top portada"
                            alt="Imagen representativa de una novela">

                        <div class="card-body">
                            <h3 class="card-title h5">El mundo de las historias</h3>
                            <p class="card-text">
                                Una novela para disfrutar de nuevas aventuras.
                            </p>
                            <p class="precio">$12.990 CLP</p>
                            <button type="button"
                                class="btn btn-primary agregar-carrito"
                                data-id="1"
                                data-titulo="El mundo de las historias"
                                data-precio="12990">
                                Agregar al carrito
                            </button>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <article class="card h-100 tarjeta-libro">
                        <img
                            src="https://placehold.co/400x260/dbeafe/1e3a8a?text=Tecnologia"
                            class="card-img-top portada"
                            alt="Imagen representativa de un libro de tecnología">

                        <div class="card-body">
                            <h3 class="card-title h5">Introducción a la tecnología</h3>
                            <p class="card-text">
                                Aprende conceptos fundamentales del mundo digital.
                            </p>
                            <p class="precio">$15.990 CLP</p>
                            <button type="button"
                                class="btn btn-primary agregar-carrito"
                                data-id="2"
                                data-titulo="Introducción a la tecnología"
                                data-precio="15990">
                                Agregar al carrito
                            </button>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <article class="card h-100 tarjeta-libro">
                        <img
                            src="https://placehold.co/400x260/d1fae5/065f46?text=Desarrollo+personal"
                            class="card-img-top portada"
                            alt="Imagen representativa de un libro de desarrollo personal">

                        <div class="card-body">
                            <h3 class="card-title h5">Crecer y aprender</h3>
                            <p class="card-text">
                                Ideas y consejos para el desarrollo personal.
                            </p>
                            <p class="precio">$9.990 CLP</p>
                            <button type="button"
                                class="btn btn-primary agregar-carrito"
                                data-id="3"
                                data-titulo="Crecer y aprender"
                                data-precio="9990">
                                Agregar al carrito
                            </button>
                        </div>
                    </article>
                </div>

            </div>
        </section>

        <!-- Novedades -->
        <section id="novedades" class="novedades p-4 rounded mb-5">
            <h2>Novedades</h2>
            <p>
                Visita regularmente nuestra librería para conocer nuevos títulos,
                autores y recomendaciones de lectura.
            </p>
        </section>

        <!-- Inicio de sesión -->

<section id="login" class="formulario-login mx-auto mb-5">
    <h2 class="titulo-seccion text-center">Iniciar sesión</h2>

    <p class="text-center">
        Ingresa tus datos para acceder a tu cuenta.
    </p>

    <form action="login.php" method="POST" id="formLogin" novalidate>

        <!-- Correo electrónico -->
        <div class="mb-3">
            <label for="correo" class="form-label">
                Correo electrónico
            </label>

            <input
                type="email"
                class="form-control"
                id="correo"
                name="correo"
                placeholder="ejemplo@correo.com"
                autocomplete="username"
                required>

            <div id="errorCorreo" class="invalid-feedback">
                Ingresa un correo electrónico válido.
            </div>
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <label for="contrasena" class="form-label">
                Contraseña
            </label>

            <input
                type="password"
                class="form-control"
                id="contrasena"
                name="contrasena"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required>

            <div id="estadoContrasena" class="form-text">
                La contraseña debe contener al menos 8 caracteres,
                una mayúscula, una minúscula, un número y un símbolo.
            </div>

            <div id="errorContrasena" class="invalid-feedback">
                La contraseña no cumple los requisitos de seguridad.
            </div>
        </div>

        <!-- Mensaje general -->
        <div id="mensajeLogin"
             class="alert alert-danger d-none"
             role="alert">
            Revisa los datos ingresados.
        </div>

        <button type="submit" class="btn btn-success w-100">
            Iniciar sesión
        </button>

        <p class="text-center mt-3">
            ¿No tienes una cuenta?
            <a href="registro.php">Regístrate aquí</a>
        </p>

    </form>
</section>

<!-- Archivo de validación -->
<script src="js/validacion-login.js"></script>

                <button type="submit" class="btn btn-success w-100">
                    Ingresar
                </button>

                <p class="text-center mt-3">
                    ¿No tienes una cuenta?
                    <a href="registro.php">Regístrate aquí</a>
                </p>
            </form>
        </section>

        <!-- Contacto -->
        <section id="contacto" class="text-center mb-4">
            <h2>Contacto</h2>
            <p>¿Tienes dudas? Escríbenos a contacto@mundodeletras.cl</p>
        </section>

    </main>

    <!-- Pie de página -->
    <footer class="pie-pagina text-center py-4">
        <p class="mb-0">
            &copy; 2026 Librería Mundo de Letras. Todos los derechos reservados.
        </p>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    <!-- JavaScript del carrito -->
    <script src="js/carrito.js"></script>

</body>
</html>
