$(function() {
    const pathArray = window.location.pathname.split('/').filter(p => p);
    const openIndex = pathArray.indexOf('open');
    if (openIndex === -1 || pathArray.length <= openIndex + 1) return;
    
    const itemId = pathArray[openIndex + 1];
    let currentIndex = parseInt(pathArray[openIndex + 2]) || 0;

    $.ajax({
        url: `items/get_pictures_service/${itemId}`,
        method: 'GET',
        dataType: 'json',
        success: function(pictures) {
            if (pictures && pictures.length > 0) {
                $("#main-static-card").hide();
                $("#php-gallery-backup").hide();

                const itemsHTML = pictures.map((pic, i) => `
                    <div class="carousel-item ${i === currentIndex ? 'active' : ''}">
                        <div class="ratio ratio-16x9 bg-white">
                            <img src="${pic.picture_path}" class="d-block w-100 h-100 object-fit-contain">
                        </div>
                    </div>`).join('');

                const thumbnailsHTML = pictures.map((pic, i) => `
                    <div class="col-4 col-md-2">
                        <a href="#" data-bs-target="#itemCarousel" data-bs-slide-to="${i}"
                            class="ratio ratio-16x9 d-block rounded overflow-hidden shadow-sm thumbnail-link ${i === currentIndex ? 'border border-secondary border-2' : ''}">
                            <img src="${pic.picture_path}" class="object-fit-contain bg-white">
                        </a>
                    </div>`).join('');

                $("#carousel-placeholder").html(`
                    <div id="itemCarousel" class="carousel slide shadow-sm mb-4 overflow-hidden">
                        <div class="carousel-inner">${itemsHTML}</div>
                        <button class="carousel-control-prev" data-bs-target="#itemCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                        <button class="carousel-control-next" data-bs-target="#itemCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                    </div>
                    
                    <div class="mb-4" id="ajax-gallery">
                        <small class="text-muted d-block mb-2">Additional Images</small>
                        <div class="row g-2">${thumbnailsHTML}</div>
                    </div>
                `);

                $("#ajax-gallery a").on("click", function(e) {
                    e.preventDefault();
                });

                $('#itemCarousel').on('slid.bs.carousel', function (e) {
                    $('.thumbnail-link').removeClass('border border-secondary border-2');
                    $('.thumbnail-link').eq(e.to).addClass('border border-secondary border-2');
                });
            }
        }
    });
});