<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item collapsed">
            <a class="nav-link " href="{{ route('dashboard') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('user') }}">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
        </li><!-- End User Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('room.index') }}">
                <i class="bi bi-people"></i>
                <span>Group</span>
            </a>
        </li><!-- End Room Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('tst.index') }}">
                <i class="bi bi-people"></i>
                <span>Suplier Tour</span>
            </a>
        </li><!-- End ts tour Nav -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('pst.index') }}">
                <i class="bi bi-people"></i>
                <span>Personal Tour</span>
            </a>
        </li><!-- End user tour Nav -->

        <li class="nav-heading">Pages</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign out</span>
            </a>
        </li><!-- End Login Page Nav -->

    </ul>

</aside>