<?php

if(session_status() === PHP_SESSION_NONE){

    session_start();

}

?>

<!-- SIDEBAR -->

<div class="sidebar">

    <!-- LOGO -->

    <div class="sidebar-logo">

        <h2>

            <i class="bi bi-grid-1x2-fill"></i>
            Segumiento KPIs

        </h2>

    </div>

    <!-- MENU -->

    <ul class="sidebar-menu">

        <!-- DASHBOARD (HOME) -->

        <li>

            <a
            href="/CRUD/dashboard/index.php"

            class="<?php
            echo basename($_SERVER['PHP_SELF']) == 'index.php'
            &&
            strpos($_SERVER['REQUEST_URI'], 'dashboard')
            ? 'active'
            : '';
            ?>">

                <i class="bi bi-house-fill"></i>

                Home

            </a>

        </li>

        <!-- SOLO ADMIN: USUARIOS Y TICKETS -->

        <?php if($_SESSION['rol'] == 'admin'){ ?>

        <li>

            <a href="/CRUD/usuarios/index.php"

            class="<?php
            echo strpos(
                $_SERVER['REQUEST_URI'],
                'usuarios'
            )
            ? 'active'
            : '';
            ?>">

                <i class="bi bi-people-fill"></i>

                Usuarios

            </a>

        </li>
        
        <li>

            <a
            href="/CRUD/tickets/index.php"

            class="<?php
            echo basename($_SERVER['PHP_SELF']) == 'index.php'
            &&
            strpos($_SERVER['REQUEST_URI'], 'tickets')
            ? 'active'
            : '';
            ?>">

                <i class="bi bi-ticket-detailed-fill"></i>

                Tickets

            </a>

        </li>

        <?php } ?>

        <!-- ESTADÍSTICAS -->

        <li>

            <a
            href="/CRUD/tickets/dashboard.php"

            class="<?php
            echo basename($_SERVER['PHP_SELF']) == 'dashboard.php'
            &&
            strpos($_SERVER['REQUEST_URI'], 'tickets')
            ? 'active'
            : '';
            ?>">

                <i class="bi bi-bar-chart-fill"></i>

                Estadisticas

            </a>

        </li>

       <!-- SOLO ADMIN: ADMINISTRACIÓN -->
        <?php if($_SESSION['rol'] == 'admin'){ ?>
        <li>
            <a href="/CRUD/administracion/index.php">
                <i class="bi bi-shield-lock-fill"></i>
                Administración
            </a>
        </li>
        <?php } ?>

        <!-- CONFIGURACION -->
        
        <li>
            <a href="/CRUD/config/config_dash/index.php">
                <i class="bi bi-gear-fill"></i>
                Configuración
            </a>
        </li>

    </ul>

    <!-- FOOTER -->

    <div class="sidebar-footer">

        <div class="user-info">

            <i class="bi bi-person-circle"></i>

            <div>

                <strong>

                    <?php
                    echo $_SESSION['user'];
                    ?>

                </strong>

                <small>

                    <?php
                    echo ucfirst($_SESSION['rol']);
                    ?>

                </small>

            </div>

        </div>

        <!-- LOGOUT -->

        <a
        href="/CRUD/auth/logout.php"
        class="btn-logout-sidebar">

            <i class="bi bi-box-arrow-left"></i>

            Salir

        </a>

    </div>

</div>