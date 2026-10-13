$(function () {
    var $sortableContainer = $("#sortable-images");
    if ($sortableContainer.length === 0) return;
    
    var itemId = $sortableContainer.data('item-id');
    var isUpdatingOrder = false; 

    $sortableContainer.sortable({
        tolerance: 'pointer',
        update: function(event, ui) {
            updateOrderAndSend();
        }
    });

    $(document).on('submit', '.js-move-form', function(e) {
        e.preventDefault(); 
        
        if (isUpdatingOrder) return; 
        
        let $form = $(this);
        let direction = $form.find('input[name="direction"]').val();
        let $currentItem = $form.closest('.sortable-item');

        if (direction === 'left') {
            let $prevItem = $currentItem.prev('.sortable-item');
            if ($prevItem.length) {
                $currentItem.insertBefore($prevItem); 
                updateOrderAndSend(); 
            }
        } else if (direction === 'right') {
            let $nextItem = $currentItem.next('.sortable-item');
            if ($nextItem.length) {
                $currentItem.insertAfter($nextItem); 
                updateOrderAndSend(); 
            }
        }
    });

    $(document).on('submit', '.js-delete-form', function(e) {
        e.preventDefault(); 
        var priority = $(this).find('input[name="priority"]').val();
        $('#modalDeletePriority').val(priority);
        var myModal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
        myModal.show();
    });

    $('#modalDeleteForm').on('submit', function(e) {
        e.preventDefault(); 
        var priority = $('#modalDeletePriority').val();
        
        $.ajax({
            url: 'items/delete_image_service',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                item_id: itemId,
                priority: priority
            }),
            success: function(response) {
                $('.sortable-item').filter(function() {
                    return $(this).find('input[name="priority"]').val() == priority;
                }).remove();
                
                updateButtonStates(); 

                var myModalEl = document.getElementById('deleteImageModal');
                var modal = bootstrap.Modal.getInstance(myModalEl);
                modal.hide();
            }
        });
    });

    function updateOrderAndSend() {
        isUpdatingOrder = true; 

        var newOrder = [];
        $('.sortable-item').each(function() {
            newOrder.push($(this).data('picture-path'));
        });
        $('.js-btn-left, .js-btn-right').prop('disabled', true);

        $.ajax({
            url: 'items/update_priorities_service',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                item_id: itemId,
                order: newOrder
            }),
            success: function() {
                updateButtonStates();
            },
            complete: function() {
                isUpdatingOrder = false; 
            }
        });
    }

    function updateButtonStates() {
        let items = $('.sortable-item');
        items.each(function(index) {
            $(this).find('.js-btn-left').prop('disabled', index === 0);
            $(this).find('.js-btn-right').prop('disabled', index === items.length - 1);
            $(this).find('input[name="priority"]').val(index);
        });
    }
});