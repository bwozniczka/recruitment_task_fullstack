import React from "react"

export const Loading = () => {
  return (
    <div className="container mt-5 text-center">
      <div className="spinner-border text-primary" role="status">
        <span className="sr-only">Ładowanie...</span>
      </div>
    </div>
  )
}
