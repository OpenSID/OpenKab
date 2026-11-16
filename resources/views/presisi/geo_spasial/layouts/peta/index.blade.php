var coordinateRequestSequence = 0;
var popupSkemaRequest = null;
if (window.PopupSkema) window.PopupSkema.ikutiUkuranPeta(map);
GetListCoordinates();

@include('presisi.geo_spasial.layouts.peta.coordinate')
@include('presisi.geo_spasial.layouts.peta.kabupaten')
@include('presisi.geo_spasial.layouts.peta.kecamatan')
@include('presisi.geo_spasial.layouts.peta.desa')

