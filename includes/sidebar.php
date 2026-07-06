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

        <!-- DASHBOARD -->

        <li>
            <a
            href="../dashboard/index.php"
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

        <!-- USUARIOS -->

        <li>

        <?php if($_SESSION['rol'] == 'admin'){ ?>

            <a href="../usuarios/index.php"

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
    href="../tickets/index.php"

    class="<?php

echo
basename($_SERVER['PHP_SELF'])
== 'index.php'
&&
strpos(
    $_SERVER['REQUEST_URI'],
    'tickets'
)
?
'active'
:
''
?>">
        <i class="bi bi-ticket-detailed-fill"></i>
        Tickets
    </a>
</li>

<?php } ?>

<li>
    <a
    href="../tickets/dashboard.php"
    class="<?php
echo
basename($_SERVER['PHP_SELF'])
== 'dashboard.php'
&&
strpos(
    $_SERVER['REQUEST_URI'],
    'tickets'
)

?

'active'

:

''

?>">

        <i class="bi bi-bar-chart-fill"></i>

        Estadisticas

    </a>

</li>

        <!-- SOLO ADMIN -->

        <?php if($_SESSION['rol'] == 'admin'){ ?>

        <li>

            <a href="#">

                <i class="bi bi-shield-lock-fill"></i>

                Administración

            </a>

        </li>

        <?php } ?>

        <!-- CONFIGURACION -->

        <li>

            <a href="#">

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
        href="../auth/logout.php"
        class="btn-logout-sidebar">

            <i class="bi bi-box-arrow-left"></i>

            Salir

        </a>

    </div>

</div>

