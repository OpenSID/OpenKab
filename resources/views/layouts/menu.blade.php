<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li>
<li class="nav-item {{ Request::is('users*') ? 'menu-open' : '' }}">
	<a href="#" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
	   <i class="nav-icon fas fa-cog"></i>
	   <p>
		  Pengaturan
		  <i class="right fas fa-angle-left"></i>
	   </p>
	</a>
	<ul class="nav nav-treeview">
	   <li class="nav-item">
		  <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
			 <i class="far fa-circle nav-icon"></i>
			 <p>Pengguna</p>
		  </a>
	   </li>
	</ul>
 </li>