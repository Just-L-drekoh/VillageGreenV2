import React from "react";

const ProductList = ({ products }) => {
  return (
    <ul className="space-y-4">
      {products.map((product) => (
        <li
          key={product.id}
          className="flex items-center p-4 border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:scale-[1.02] transition-transform duration-200"
        >
          <div className="flex-grow">
            <h3 className="text-lg font-semibold text-gray-800">
              {product.label}
            </h3>
          </div>
          <div className="text-sm text-gray-500">
            <p className="font-medium text-indigo-600">{product.price} €</p>
            <p>{product.stock} en stock</p>
          </div>
          <a
            href={`http://localhost/product/${product.slug}`}
            className="flex items-center text-indigo-500 hover:text-indigo-700 transition-colors duration-200"
          >
            Voir en détail
          </a>
        </li>
      ))}
    </ul>
  );
};

export default ProductList;
