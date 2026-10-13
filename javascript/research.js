$(function () {
    let searchInUrl = null;
    let currentSearchState = "";
    let encodedResearch = $('#top-main').data("search") ?? null;

    if (encodedResearch) {
        fetch('items/decode_url_service', {
            method: 'POST',
            headers: {
                "Content-type": "application/json"
            },
            body: JSON.stringify({
                user_research_encoded: encodedResearch
            })
        }).then(response => response.json())
        .then(data => {
                searchInUrl = data.decoded_research ?? null;
                let textValue = searchInUrl.user_input;
                let categoryValue = searchInUrl.selected_category
                if (searchInUrl) {
                    $("#search").val(textValue);
                    $("#category-select").val(categoryValue);
                    $( "#search" ).trigger( "keyup" );
                }
            }).catch(error => console.error("Erreur fetch :", error));
    }

    $('#top-main').prepend(`<div class="input-group custom-search-group mb-4">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="search" class="form-control text-truncate" placeholder="Search items..." autocomplete="off">
                                <select class="form-select" style="max-width: 250px;" name="category-id" id="category-select">
                                    <option value="">Loading categories...</option>
                                </select>
                            </div>`);

    // recup des categories via un service
    fetch('items/get_categoriesList_service')
        .then(response => response.json())
        .then(data => {
            let $select = $('#category-select');
            $select.empty(); //on enlève le loading
            $select.append('<option value="">All categories</option>');

            if (data) {
                data.categoriesList.forEach(function(category) {
                    $select.append(`<option value="${category.id}">${category.name}</option>`);
                });
            } else {
                console.error("aucune données", data);
            }
        })
        .catch(error => {
            console.error("Erreur lors du chargement des catégories :", error);
            $('#category-select').html('<option value="">Error loading categories</option>');
        });

    $("#search").on("keyup", async function () {
        let user_entry = $(this).val();
        let selected_category = $("#category-select").val() ?? "";
        const isBrowse = window.location.href.includes("items/display");
        let data = null;

        try {
            data = await $.ajax({ url: "items/search_service", method: "POST", dataType: "json", data: { entry: user_entry, categoryId: selected_category, source: isBrowse ? "from-browse" : "from-my-items" }});
            
        } catch (xhr) {
            console.error("AJAX failed");
            console.error("status:", xhr.status);
            console.error("responseText:", xhr.responseText);
        }

        const noResults = isBrowse ? (data.participated.length == 0 && data.others.length == 0): (data.userItems.length == 0 && data.userNonActiveItems.length == 0 && data.userSoldedItems.length == 0);

        currentSearchState = data.encoded;
        let $listParticipated = $("#participated-list");
        let $listOthers = $("#others-list");
        $listParticipated.empty();
        $listOthers.empty();

        let $userItemsList = $("#user-items-list");
        let $userNonActiveList = $("#user-non-active-list");
        let $userSoldedItemsList = $("#user-solded-items-list");
        $userItemsList.empty();
        $userNonActiveList.empty();
        $userSoldedItemsList.empty();
        
        if (isBrowse) {
            if (data.participated.length > 0) {
                $("#title-participating").show();
                data.participated.forEach(item => {
                    $listParticipated.append(generateItemCardHtml(item, "from-browse", currentSearchState));
                });
            } else {
                $("#title-participating").hide();
            }

            if(data.others.length > 0) {
                $("#title-others").show();
                data.others.forEach(item => {
                    $listOthers.append(generateItemCardHtml(item, "from-browse", currentSearchState));
                });
            } else {
                $("#title-others").hide();
            }
        } else {
            if (data.userItems.length > 0) {
                $("#title-active").show();
                data.userItems.forEach(item => {
                    $userItemsList.append(generateItemCardHtml(item, "from-my-items", currentSearchState))
                });
            } else {
                $("#title-active").hide();
            }

            if (data.userNonActiveItems.length > 0) {
                $("#title-closed").show();
                data.userNonActiveItems.forEach(item => {
                    $userNonActiveList.append(generateItemCardHtml(item, "from-my-items", currentSearchState))
                });
            } else {
                $("#title-closed").hide();
            }

            if (data.userSoldedItems.length > 0) {
                $("#title-sold").show();
                data.userSoldedItems.forEach(item => {
                    $userSoldedItemsList.append(generateItemCardHtml(item, "from-my-items", currentSearchState))
                });
            } else {
                $("#title-sold").hide();
            }
        }

        if (noResults) {
            $("#no-results-message").remove();
            $("#top-main").append(generateNoResultHtml());
        } else {
            $("#no-results-message").remove();
        }
    });
});

$(document).on("change", "#category-select", function () {
    $("#search").trigger("keyup");
});

function generateNoResultHtml() { // pt le mettre direct dans le html et le hide/show
    return `
        <div id="no-results-message" class="col-12 text-center my-5 py-5">
            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3 text-muted">No items match your search.</h4>
            <p class="text-secondary">Try adjusting your filters or search terms.</p>
        </div>
    `;
}

function generateItemCardHtml(item, source, currentSearchState) {
    // Gestion des badges de gauche (Gagnant / Enchérisseur)
    let bidderBadge = "";
    if (item.isHighestBidder) {
        bidderBadge = `<span class="badge rounded-pill bg-success shadow-sm p-2">
                        <i class="bi bi-trophy me-1"></i>Highest Bidder
                    </span>`;
    } else if (item.hasBidByUser) {
        bidderBadge = `<span class="badge rounded-pill bg-secondary shadow-sm p-2">
                        <i class="bi bi-tag me-1"></i>Bidder
                    </span>`;
    }

    // Gestion des badges de droite (Type de vente)
    let typeBadges = "";
    if (item.is_auction == 1) {
        typeBadges += `<span class="badge rounded-pill bg-warning shadow-sm p-2"><i class="bi bi-tag me-1"></i>Auction</span>`;
    }
    if (item.has_buy_now == 1) {
        typeBadges += `<span class="badge rounded-pill bg-primary shadow-sm p-2"><i class="bi bi-bag me-1"></i>Buy Now</span>`;
    }

    // Badge nombre d'images
    let imagesBadge = "";
    if (item.pictures && item.pictures.length > 1) {
        imagesBadge = `<span class="badge rounded-pill bg-dark bg-opacity-75 shadow-sm">
                        <i class="bi bi-images me-1"></i>${item.pictures.length} images
                    </span>`;
    }

    // Gestion du prix affiché
    let priceDisplay = "";
    if (item.has_buy_now == 0 && item.is_auction == 1) {
        priceDisplay = `<span class="fs-5 fw-bold text-warning">€ ${item.starting_bid}</span>`;
    } else {
        priceDisplay = `<span class="fs-5 fw-bold text-primary">€ ${item.buy_now_price}</span>`;
    }

    // Affichage de l'enchère actuelle
    let currentBidDisplay = "";
    if (item.has_bids == 1 && item.max_bid != item.buy_now_price) {
        currentBidDisplay = `<div class="text-end">
                                <small class="text-muted d-block">Current bid</small>
                                <span class="text-success">€ ${item.max_bid}</span>
                            </div>`;
    }

    // Couleur du temps restant
    let timeClass = "text-warning";
    if (item.remaining_time === "Closed" || item.remaining_time === "Expired") {
        timeClass = "text-muted";
    } else if (item.remaining_time.includes("d")) {
        timeClass = "text-success";
    }

    // Construction du HTML final
    return `
    <div class="col">
        <div class="card h-100 text-white border-0 shadow-sm position-relative">
            <div class="ratio ratio-16x9">
                <img src="${item.pictures[0].picture_path}" class="card-img-top object-fit-cover" alt="${item.title}">
                <div class="card-img-overlay">
                    <div class="position-absolute top-0 start-0 d-flex p-2">${bidderBadge}</div>
                    <div class="position-absolute top-0 end-0 d-flex p-2"><div class="d-grid gap-1">${typeBadges}</div></div>
                    <div class="position-absolute bottom-0 end-0 d-flex p-2">${imagesBadge}</div>
                </div>
            </div>
            <div class="card-body bg-body-secondary d-flex flex-column">
                <h5 class="card-title fw-bold text-truncate">
                    <a href="items/open/${item.id}/${source}/${currentSearchState}" class="text-decoration-none text-reset stretched-link">
                        ${item.title}
                    </a>
                </h5>
                <small class="card-subtitle text-muted mb-2">by ${item.owner_pseudo}</small>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    ${priceDisplay}
                    ${currentBidDisplay}
                </div>
                <div class="mt-auto">
                    <small class="${timeClass}"><i class="bi bi-clock me-1"></i>${item.remaining_time}</small>
                </div>
            </div>
        </div>
    </div>`;
}