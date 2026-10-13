let categories;
let rules;

$(async function() {
    let success = await getData();
    success = await getRules();
    

    if (success) {
        initialize();
    }
})

async function getData() {
    try {
        categories = await $.getJSON("category/get_categories_service");
        return true;
    } catch (error) {
        console.error("Erreur lors de la récupération des categories :", error);
        return false;
    }
}

async function getRules() {
    try {
        rules = await $.getJSON("category/get_rules_service");
        return true;
    } catch (error) {
        console.error("Erreur lors de la récupération des règles métiers :", error);
        return false;
    }
}

function initialize() {
    const addButtonHtml = `
        <div class="d-flex justify-content-end pt-3 mt-3 border-top border-secondary" id="add">
            <button class="btn btn-primary btn-sm rounded px-3 py-2" onclick="add(0, 0)">+</button>
        </div>`;
    const listRows = $("#categories-list");
    listRows.empty();

    categories.forEach(category => {
        listRows.append(generateRow(category));
    });

    listRows.append(addButtonHtml);

    Sortable.create(listRows[0], { // [0] pour sortir l'element DOM jqery car sortable ne le comprend pas
        animation: 150,
        draggable: ".category-row", //pour ne pas cibler la bouton add
        ghostClass: 'bg-dark',
        onEnd: function () {
            updatePriority();
        }
    });

}

function add(id, priority) {
    const $holder = $("#add");
    priority = categories.length + 1
    $holder.html(`<div class="w-100 d-flex gap-2 align-items-center">
                        <div class="flex-grow-1">
                            <input type="text" id="input-${id}" class="form-control d-inline-block py-1" placeholder="New category...">
                            <div id="error-${id}" class="text-danger small mt-1"></div>
                        </div>
                        <button type="button" id="cancel-add" class="btn btn-sm btn-outline-secondary">Cancel</button>
                    </div>`);

    const $input = $(`#input-${id}`);
    const $error = $(`#error-${id}`);
    const $cancelBtn = $('#cancel-add');

    let isCancelling = false;

    $cancelBtn.on("mousedown", function(e) {
        isCancelling = true;
        resetToAdd($holder);
    });

    $input.focus().on("blur", async function() {
        if (isCancelling) return;
        const $newName = $input.val().trim();

        if ($newName == "") {
            return resetToAdd($holder);
        }

        if (!validateLength($newName, rules.name.min, rules.name.max)) {
            setInvalid($input, $error, `Min ${rules.name.min} and max ${rules.name.max} chars.`);
            setTimeout(() => $input.focus(), 10);
        } else {
            fetch("category/is_category_name_unique_service", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    category_name: $newName,
                    id: id
                })
            }).then(res => res.json())
            .then(data => {
                if (isCancelling) return;
                if (!data.isUnique) {
                    setInvalid($input, $error, `This name is already taken.`);
                } else {
                    fetch("category/persiste_service", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            category_name: $newName,
                            priority: priority
                        })
                    }).then(res => res.json())
                    .then(newCategory => {
                        newCategory.nb_item_assigned = 0;
                        setValid($input, $error);
                        resetToAdd($holder);
                        const newRowHtml = generateRow(newCategory);
                        $holder.before(newRowHtml);
                        categories.push(newCategory);
                    });
                }
            });
        }
    });
}

function edit(id, oldName, count) {
    const inputHtml = `
        <input type="text" class="form-control py-1" id="input-${id}" value="${oldName}" required autocomplete="off">
        <div class="invalid-feedback" id="error-${id}"></div>`;

    const $holder = $(`#name-holder-${id}`);
    $holder.html(inputHtml);

    const $input = $(`#input-${id}`);
    const $error = $(`#error-${id}`);

    $input.focus().on("blur", async function() {
        const $newName = $input.val().trim();

        if ($newName == oldName) {
            return resetToText($holder, oldName, count);
        }

        if (!validateLength($newName, rules.name.min, rules.name.max)) {
            setInvalid($input, $error, `Min ${rules.name.min} and max ${rules.name.max} chars.`);
        } else {
            fetch("category/is_category_name_unique_service", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    category_name: $newName,
                    id: id
                })
            }).then(res => res.json())
            .then(data => {
                if (!data.isUnique) {
                    setInvalid($input, $error, `This name is already taken.`);
                } else {
                    fetch("category/update_service", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            category_name: $newName,
                            id: id,
                            count: count
                        })
                    }).then(res => res.json())
                    .then(category => {
                        setValid($input, $error);
                        const newRowHtml = generateRow(category);
                        $("#row-" + category.id).replaceWith(newRowHtml);
                    });
                }
            });
        }
    });
}

async function updatePriority() {
    const newOrder = [];
    
    // On parcourt chaque ligne pour récupérer son ID dans l'ordre actuel du DOM
    $("#categories-list .category-row").each(function(index) {
        const id = $(this).data("id"); // Récupère l'ID via data-id="${category.id}"
        newOrder.push({
            id: id,
            priority: index + 1 // La priorité devient sa position (1, 2, 3...)
        });
    });

    try {
        const response = await fetch("category/update_priorities_service", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ orders: newOrder })
        });
        
        if (!response.ok) console.error("Erreur de sauvegarde des priorités");
    } catch (e) {
        console.error("Erreur réseau", e);
    }
}

function resetToText(holder, name, count) {
    holder.html(`${name} <span class="text-secondary">(${count})</span>`);
}

function resetToAdd(holder) {
    holder.html(`<button class="btn btn-primary btn-sm rounded px-3 py-2" onclick="add(0, 0)">+</button>`)
}

function validateLength(field, minLength, maxLength) {
    const fieldLength = field.length;

    return fieldLength >= minLength && fieldLength <= maxLength;
}

function setInvalid(field, errorField, errorMessage) {
    field.removeClass("is-valid").addClass("is-invalid");
    errorField.text(errorMessage);
}

function setValid(field, errorField) {
    field.removeClass("is-invalid");
    errorField.text("");
}

function resetToBlank() {
        return `<div class="d-flex justify-content-end pt-3 mt-3 border-top border-secondary" id="name-holder-0">
                    <button class="btn btn-primary btn-sm rounded px-3 py-2" onclick="addHandler(0, 0)">+</button>
                </div>`;
}

function generateRow(category) {
    const count = category.nb_item_assigned ?? 0;
    const isDisabled = count > 0 ? "disabled" : "";

    return `
        <div class="category-row d-flex align-items-center justify-content-between p-2 mb-1 rounded" data-id="${category.id}" id="row-${category.id}">
            <div class="d-flex align-items-center">
                <i class="bi bi-grip-vertical drag-handle me-2 text-secondary"></i>
                <span class="category-name text-light" id="name-holder-${category.id}"> ${category.name}
                    <span class="text-secondary">(${category.nb_item_assigned})</span>
                    <div class="invalid-feedback" id="error-${category.id}"></div>
                </span>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-dark-custom border-secondary text-secondary px-3" onclick="edit(${category.id}, '${category.name}', ${category.nb_item_assigned})">Edit</button>
                <button class="btn btn-sm btn-dark-custom border-danger text-danger px-3" ${isDisabled} onclick="showDeleteModal(${category.id})">Delete</button>
            </div>
        </div>`;
}

function showDeleteModal(id) {
    $('#deletionModal').remove();

    const modalHtml = `
    <div class="modal fade" id="deletionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete alert</h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure that you want to delete this category ?</p>
                    <p class="fw-bold">you can't undo the deletion.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger" data-bs-dismiss="modal">Delete</button>
                </div>
            </div>
        </div>
    </div>`;

    $('body').append(modalHtml);

    const modalVisual = $('#deletionModal');
    const Modal = new bootstrap.Modal(modalVisual);
    Modal.show();

    $('#confirmDeleteBtn').on('click', function() {
        fetch("category/delete_service", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id: id
            })
        }).then(() => {
            $(`#row-${id}`).fadeOut(300, function() {
                $(this).remove();
            });
            categories = categories.filter(category => category.id != id);
        });
    });

}