<div class="addel-container">
    <div class="addel-target">
        <button type="button" class="btn btn-danger addel-del " data-addel-delete>Delete</button>

        <div class="addel-container1">
            <div class="addel-target">
                <button type="button" class="btn btn-danger addel-del " data-addel-delete="1">Delete</button>
                <input type="text" id="name" name="[name]" class="form-control"
                       value="">
            </div>
            <button type="button" class="btn btn-success btn-block" data-addel-add="1">
                <i class="fa fa-plus"></i>
            </button>
        </div>

    </div>
    <button type="button" class="btn btn-success addel-add">Add</button>
</div>

<script>
    $(document).ready(function () {
        $('.addel-container').addel({
            // optional options object
        });
        $('.addel-container1').addel({
            // optional options object
        });
    });
</script>