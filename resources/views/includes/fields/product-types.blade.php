<div class="form-group col-md-3">
    <label for="filter-product-types" class="control-label">Product Types</label>
    <select id="filter-product-types" name="product_types[]"
            multiple
            class="form-control my-select2 dt-filter">
        @foreach(\App\Models\ProductType::all() as $product_type)
            <option value="{{$product_type->id}}">{{$product_type->title}}</option>
        @endforeach
    </select>
</div>
