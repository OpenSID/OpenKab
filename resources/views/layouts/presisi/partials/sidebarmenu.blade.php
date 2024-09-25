<!-- Sidebar Menu -->
<nav class="mt-2 pb-5">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item mt-2 mb-2">
            <a class="nav-link {{ Route::getCurrentRoute()->getName()== 'presisi.index' ? 'active' : '' }}" href="{{ url('presisi') }}">
                <p>
                    Demografi
                </p>
            </a>
        </li>
        <li class="nav-item mt-2 mb-2">
            <a class="nav-link {{ Route::getCurrentRoute()->getName()== 'presisi.sosial' ? 'active' : '' }} " href="{{ url('presisi/sosial') }}">
                <p>
                    Sosial
                </p>
            </a>
        </li>
        <li class="nav-item mt-2 mb-2">
            <a class="nav-link {{ Route::getCurrentRoute()->getName()== 'presisi.kependudukan' ? 'active' : '' }}" href="{{ url('presisi/kependudukan') }}">
                <p>
                    Kependudukan
                </p>
            </a>
        </li>
        <li class="nav-item mt-2 mb-2">
            <a class="nav-link {{ Route::getCurrentRoute()->getName()== 'presisi.ekonomi' ? 'active' : '' }}" href="{{ url('presisi/ekonomi') }}">
                <p>
                    Ekonomi
                </p>
            </a>
        </li>

        <li class="nav-item mt-2 mb-2">
            <a class="nav-link {{ Route::getCurrentRoute()->getName()== 'presisi.bantuan' ? 'active' : '' }} " href="{{ url('presisi/bantuan') }}">
                <p>
                    Kesehatan
                </p>
            </a>
        </li>        
    </ul>
</nav>
