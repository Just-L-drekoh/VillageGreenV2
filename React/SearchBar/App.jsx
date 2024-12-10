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
      <div className="flex justify-center p-4">
        <input
          className="border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 rounded-md p-2 w-full max-w-md transition ease-in-out duration-150"
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Rechercher un produit"
        />
      </div>

      <div className="container mx-auto p-6">
        {products.length === 0 ? (
          <div className="text-center text-gray-500">
            <p>Aucun produit trouvé</p>
          </div>
        ) : (
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
                  <p className="font-medium text-indigo-600">
                    {product.price} €
                  </p>
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
        )}
      </div>
    </>
  );
}

export default App;
