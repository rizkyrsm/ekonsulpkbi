<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ekonsul PKBI</title>
    <link rel="icon" type="image/png" href="{{ asset('https://pkbi-jatim.or.id/wp-content/uploads/2021/12/cropped-Logo-PKBI-Jatim.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="https://pkbi-jatim.or.id/wp-content/uploads/2024/07/Untitled-design-1.png" alt="Logo" width="auto" height="50" class="d-inline-block align-text-top">
            </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <span class="navbar-brand">Ekonsul PKBI Jawa Timur</span>
              </li>
            </ul>
            <span class="navbar-brand">
              <a href="{{ route('login') }}" class="nav-link">Login <i class="bi bi-door-open-fill"></i></a>
            </span>
          </div>
        </div>
      </nav>

      <div class="container-md mt-4">
        <h2 class="mb-4">Daftar Layanan</h2>
    
        <div class="d-flex flex-wrap gap-3">
            @foreach($layanans as $index => $layanan)
                <div class="card" style="width: 18rem;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $layanan->nama_layanan }}</h5>
                        <h6 class="card-subtitle mb-2 text-body-secondary">VOUCHER : PKBIJATIM</h6>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <br><s>Rp. {{ $layanan->harga_layanan }}</s>/Rp.0
                        <a href="{{ route('dashboard.keranjang', ['id' => $layanan->id_layanan]) }}" class="btn btn-success text-white card-link"><i class="bi bi-chat-heart-fill"></i> Mulai Konseling</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
  </body>
</html>