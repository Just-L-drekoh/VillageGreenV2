import React from "react"; // Optional for React 17+
import ReactDOM from "react-dom/client"; // Correct import for React 18
import App from "./App.jsx";

// Ensure the root element exists in your HTML file.
const root = ReactDOM.createRoot(document.getElementById("root"));

root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
