$(function() {
    const form = $("#edit-form, #add-form");
    const oldValue = {};

    form.find("input[name], textarea[name]").each(function () {
        if ($(this).is(':checkbox')) {
            oldValue[this.name + '_' + $(this).val()] = $(this).prop('checked');
        }else {
            oldValue[this.name] = $(this).val();
        }
    });

    $(".nav a.nav-link").on("click", function(e) {
        if (isDirty()) {
            e.preventDefault();

            const targetUrl = $(this).attr("href");
            showModal(targetUrl);
        }
    });

    $(".form-check-input").on("change", function() {
        if (countCheckedBox() > 3) {
            showCheckboxModal();
        }
    });

    function countCheckedBox() {
        let count = form.find('.form-check-input:checked').length;
        return count;
    }

    function isDirty() {
        let dirty = false;

        form.find("input[name], textarea[name]").each(function () {
            if ($(this).is(':checkbox')) {

                if ($(this).prop('checked') != oldValue[this.name + '_' + $(this).val()]) {
                    dirty = true;
                }
            } else {
                if ($(this).val() != oldValue[this.name]) {
                    dirty = true;
                }
            }
        });
        return dirty;
    }
});

function showModal(targetUrl) {
    $("#dynamicConfirmModal").remove();

    const modalHtml = `
    <div class="modal fade" id="dynamicConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Unsaved Changes</h5>
                </div>
                <div class="modal-body">
                    <p>You have modified some fields. If you leave now, your changes will be lost.</p>
                    <p class="fw-bold">Do you really want to leave ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Stay here</button>
                    <button type="button" id="confirmLeaveExecute" class="btn btn-danger">Leave without saving</button>
                </div>
            </div>
        </div>
    </div>`;

    $('body').append(modalHtml);

    const modalVisual = $('#dynamicConfirmModal');
    const Modal = new bootstrap.Modal(modalVisual);
    Modal.show();

    $('#confirmLeaveExecute').on('click', function() {
        window.location.href = targetUrl;
    });
}

function showCheckboxModal() {
    $("#checkboxModal").remove();

    const modalHtml2 = `
    <div class="modal fade" id="checkboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Checkbox alert</h5>
                </div>
                <div class="modal-body">
                    <p>Can't have more than 3 checkbox checked.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div>`;

    $('body').append(modalHtml2);

    const modalVisual2 = $('#checkboxModal');
    const Modal2 = new bootstrap.Modal(modalVisual2);
    Modal2.show();

}