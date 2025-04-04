function ajustarPiePagina() {
    const contenidoPagina = document.querySelector('.contenido-pagina');
    const piePagina = document.querySelector('.pie-pagina');
    const alturaVentana = window.innerHeight;
    const alturaContenido = contenidoPagina.offsetHeight;
    const alturaPiePagina = piePagina.offsetHeight;
  
    if (alturaContenido + alturaPiePagina <= alturaVentana) {
      piePagina.style.position = 'absolute';
      piePagina.style.bottom = '0';
      piePagina.style.width = '100%';
    } else {
      piePagina.style.position = 'static';
      piePagina.style.bottom = 'auto';
      piePagina.style.width = 'auto';
    }
  }
  
  ajustarPiePagina();
  window.addEventListener('resize', ajustarPiePagina);