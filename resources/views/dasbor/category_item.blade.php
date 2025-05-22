<div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-{{ $colors[$loop->index % count($colors)] }}">
              <div class="inner">
                <h3>{{ $value }}</h3>

                <p>{{ $text }} </p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="{{ $url }}" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

        