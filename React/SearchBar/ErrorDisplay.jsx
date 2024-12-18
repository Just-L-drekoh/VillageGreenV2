import React from "react";

const ErrorDisplay = ({ error }) => {
  return (
    <div className="text-center text-red-500">
      <p>{error}</p>
    </div>
  );
};

export default ErrorDisplay;
