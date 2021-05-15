<div id="custom-search-input">
    <div class="input-group">
        <input id="search" name="search" type="text" class="form-control" placeholder="Search" />
    </div>
</div>
<script type="text/javascript">
    var route = "{{ url('account/autocomplete') }}";
    $('#search').typeahead({
        source:  function (term, process) {
            return $.get(route, { term: term }, function (data) {
                return process(data);
            });
        }
    });
</script>
<style>
    .typeahead.dropdown-menu {
        background-color: white;
        margin: 5px;
        padding: 5px;
        z-index: 2002;
        position: absolute;
        border-radius: 8px;
        box-shadow: 0 5px 10px rgb(0 0 0 / 20%);
    }
</style>