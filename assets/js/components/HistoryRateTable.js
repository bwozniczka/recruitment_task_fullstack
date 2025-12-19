import React, { useState, useEffect } from "react"
import { useParams, Link } from "react-router-dom"

export const HistoryRateTable = () => {
  const { currency } = useParams()

  const [history, setHistory] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    fetch(`/api/rates/${currency}/history`)
      .then((response) => response.json())
      .then((data) => {
        if (data.history) {
          setHistory(data.history)
        }
        setLoading(false)
      })
      .catch((error) => {
        console.error("Error fetching historical rates:", error)
        setLoading(false)
      })
  }, [currency])

  if (loading) {
    return (
      <div className="container mt-5 text-center">
        <div className="spinner-border text-primary" role="status">
          <span className="sr-only">Ładowanie...</span>
        </div>
      </div>
    )
  }

  return (
    <div className="container mt-4">
      <Link to="/rates" className="btn btn-outline-primary mb-3">
        &larr; Wróć do listy kursów
      </Link>

      <div className="card shadow-sm">
        <div className="card-header bg-primary text-white">
          <h4 className="mb-0">Historia: {currency} (ostatnie 14 dni)</h4>
        </div>
        <div className="card-body p-0">
          <table className="table table-striped mb-0">
            <thead className="thead-dark">
              <tr>
                <th>Data</th>
                <th>Kurs Średni (NBP)</th>
              </tr>
            </thead>
            <tbody>
              {history.length > 0 ? (
                history.map((item, index) => (
                  <tr key={index}>
                    <td>
                      <strong>{item.date}</strong>
                    </td>
                    <td>{item.rate} PLN</td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="2" className="text-center">
                    Brak danych
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
