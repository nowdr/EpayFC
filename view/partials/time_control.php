<?php if (configuration::is_dev()): ?>
    <nav class="nav justify-content-between align-items-center p-1 bg-dark bg-opacity-75">
        <div class="text-white">
            <?= AppTime::get_current_datetime() ?>
        </div>
        <div>
            <form method="post" action="time/advance" style="display: inline;">
                <input type="hidden" name="amount" value="1">
                <input type="hidden" name="unit" value="hour">
                <button type="submit" class="btn btn-outline-warning" >+1 h</button>
            </form>
            <form method="post" action="time/advance" style="display: inline;">
                <input type="hidden" name="amount" value="1">
                <input type="hidden" name="unit" value="day">
                <button type="submit" class="btn btn-outline-warning">+1 d</button>
            </form>
            <form method="post" action="time/advance" style="display: inline;">
                <input type="hidden" name="amount" value="7">
                <input type="hidden" name="unit" value="day">
                <button type="submit" class="btn btn-outline-warning">+1 w</button>
            </form>
            <form method="post" action="time/reset" style="display: inline;">
                <button type="submit" class="btn btn-outline-danger">Reset</button>
            </form>
        </div>
    </nav>
<?php endif; ?>