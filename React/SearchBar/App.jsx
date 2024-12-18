import React from "react";
import { useState, useEffect, useCallback } from "react";

import SearchBar from "./SearchBar";
import ProductList from "./ProductList";
import ErrorDisplay from "./ErrorDisplay";

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
      console.error("API Error:", err);
      setError("Impossible de charger les produits. Veuillez réessayer.");
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, [query]);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      fetchProducts();
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [query, fetchProducts]);

  return (
    <>
      <SearchBar query={query} setQuery={setQuery} />

      <div className="container mx-auto p-6">
        {loading && (
          <div className="text-center text-gray-500">
            <p>Chargement des produits...</p>
          </div>
        )}

        {error && <ErrorDisplay error={error} />}

        {!loading && !error && products.length === 0 && query.trim() !== "" && (
          <div className="text-center text-gray-500">
            <p>Aucun produit trouvé</p>
            <a
              href={`http://localhost/product`}
              className="text-indigo-500 hover:text-indigo-700 transition-colors duration-200"
            >
              Voir tout les instruments
            </a>
          </div>
        )}

        {!loading && products.length > 0 && <ProductList products={products} />}
      </div>
    </>
  );
}

export default App;
