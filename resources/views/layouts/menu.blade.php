
<li class="nav-item">
    <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Roles</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('settings.index') }}" class="nav-link {{ Request::is('settings*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Settings</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('categories.index') }}" class="nav-link {{ Request::is('categories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('articles.index') }}" class="nav-link {{ Request::is('articles*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Articles</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('pages.index') }}" class="nav-link {{ Request::is('pages*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Pages</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('slides.index') }}" class="nav-link {{ Request::is('slides*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Slides</p>
    </a>
</li>
