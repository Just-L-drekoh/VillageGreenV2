import React, { useState, useEffect } from "react";

function App() {
  const [query, setQuery] = useState("");
  const [products, setProducts] = useState([]);

  useEffect(() => {
    if (query.trim() === "") {
      setProducts([]);
      return;
    }

    const fetchProducts = async () => {
      try {
        const response = await fetch(
          `/api/products?q=${encodeURIComponent(query)}`
        );
        if (!response.ok) {
          throw new Error("Failed to fetch products");
        }
        const data = await response.json();
        setProducts(data);
      } catch (error) {
        console.error(error);
        setProducts([]);
      }
    };

    fetchProducts();
  }, [query]);

  return (
    <>
      {/* Centering the search bar */}
      <div className="flex justify-center py-4">
        <input
          className="border border-gray-300 rounded-md px-4 py-2 w-full max-w-md"
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Rechercher un produit"
        />
      </div>

      <div className="max-w-4xl mx-auto p-4">
        {/* Products List */}
        <ul className="space-y-4">
          {products.map((product) => (
            <li
              key={product.id}
              className="flex items-center p-4 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
            >
              <div className="flex-grow">
                <h3 className="text-lg font-semibold text-gray-800">
                  {product.label}
                </h3>
              </div>
              <div className="flex-grow">
                <img
                  src={product.image.img}
                  alt={product.label}
                  className="w-32 h-32 object-cover rounded-lg"
                />
              </div>

              <div className="text-sm text-gray-500">
                <p className="font-medium text-indigo-600">{product.price} €</p>
                <p>{product.stock} en stock</p>
              </div>
              <a
                href={`http://localhost/product/${product.slug}`}
                className="flex items-center w-full"
              >
                Voir en detail
              </a>
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}

export default App;
