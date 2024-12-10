import React, { useState, useEffect, useCallback } from "react";

function App() {
  const [query, setQuery] = useState("");
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const fetchProducts = useCallback(async () => {
    if (query.trim() === "") {
      setProducts([]);
      setError("");
      return;
    }

    setLoading(true);
    setError("");

    try {
      const response = await fetch(
        `/api/search?q=${encodeURIComponent(query)}`
      );
      if (!response.ok) {
        throw new Error(
          "Une erreur est survenue lors du chargement des produits."
        );
      }
      const data = await response.json();
      setProducts(data);
    } catch (err) {
      console.error(err);
      setError("Impossible de charger les produits. Veuillez réessayer.");
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, [query]);

  // Debounce effect to reduce API calls
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchProducts();
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [query, fetchProducts]);

  return (
    <>
      {/* Barre de recherche */}
      <div className="p-4 mt-8 flex flex-col items-center">
        <h2 className="text-2xl font-semibold text-gray-800 mb-4">
          Rechercher un produit
        </h2>
        <input
          aria-label="Rechercher un produit"
          className="border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 rounded-md p-2 w-full max-w-md transition ease-in-out duration-150"
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Rechercher un produit"
        />
      </div>

      {/* Contenu principal */}
      <div className="container mx-auto p-6">
        {loading && (
          <div className="text-center text-gray-500">
            <p>Chargement des produits...</p>
          </div>
        )}

        {error && (
          <div className="text-center text-red-500">
            <p>{error}</p>
          </div>
        )}

        {!loading && !error && products.length === 0 && query.trim() !== "" && (
          <div className="text-center text-gray-500">
            <p>Aucun produit trouvé</p>
            <a
              href={`http://localhost/product`}
              className=" text-indigo-500 hover:text-indigo-700 transition-colors duration-200"
            >
              Voir tout les instruments
            </a>
          </div>
        )}

        {!loading && products.length > 0 && (
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
