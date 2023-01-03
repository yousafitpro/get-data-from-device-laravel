<div class="form-group col-md-3">
    <label for="from_date" class="control-label">From Date</label>
    <input type="date" id="from_date" name="from_date" class="form-control dt-filter"
           value="{{request('from_date')}}"
    />
</div>
<div class="form-group col-md-3">
    <label for="to_date" class="control-label">To date</label>
    <input type="date" id="to_date" name="to_date" class="form-control dt-filter"
           value="{{request('to_date')}}"
    />
</div>
