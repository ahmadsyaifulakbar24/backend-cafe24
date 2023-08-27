<table>
    <thead>
    <tr>
        <th>product_combination_id</th>
        <th>stock</th>
        <th>product_slug</th>
    </tr>
    </thead>
    <tbody>
    @foreach($product_combinations as $product_combination)
        <tr>
            <td>{{ $product_combination->id }}</td>
            <td>{{ $product_combination->stock }}</td>
            <td>{{ $product_combination->product_slug }}</td>
        </tr>
    @endforeach
    </tbody>
</table>