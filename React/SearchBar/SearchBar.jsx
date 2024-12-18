import React from "react";

const SearchBar = ({ query, setQuery }) => {
  return (
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
  );
};

export default SearchBar;
