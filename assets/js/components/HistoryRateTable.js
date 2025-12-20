import React, { useState, useEffect } from "react"
import { useParams, Link } from "react-router-dom"
import { Loading } from "./Loading"

export const HistoryRateTable = () => {
  const { currency } = useParams()

  const [history, setHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [selectedDate, setSelectedDate] = useState(
    new Date().toISOString().split("T")[0]
  )

  useEffect(() => {
    setLoading(true)
    fetch(`/api/rates/${currency}/history?date=${selectedDate}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.history) {
          setHistory(data.history)
        } else {
          setHistory([])
        }
        setLoading(false)
      })
      .catch((error) => {
        console.error("Error fetching historical rates:", error)
        setLoading(false)
      })
  }, [currency, selectedDate])

  const getTrend = (currentRate, prevRate) => {
    if (!prevRate) return <span className="text-secondary">-</span>
    if (currentRate > prevRate)
      return <span className="trend-icon text-success">▲</span>
    if (currentRate < prevRate)
      return <span className="trend-icon text-danger">▼</span>
    return <span className="trend-icon text-secondary">-</span>
  }

  if (loading) {
    return <Loading />
  }

  return (
    <div className="container mt-4">
      <Link to="/rates" className="btn btn-outline-primary mb-3">
        &larr; Wróć do listy kursów
      </Link>

      <div className="card shadow-sm">
        <div className="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 className="mb-0">Historia: {currency}</h4>
          <div className="form-group mb-0">
            <input
              type="date"
              className="form-control form-control-sm"
              value={selectedDate}
              onChange={(e) => setSelectedDate(e.target.value)}
              max={new Date().toISOString().split("T")[0]}
            />
          </div>
        </div>
        <div className="card-body p-0">
          <table className="table table-striped mb-0">
            <thead className="thead-dark">
              <tr>
                <th>Data</th>
                <th>Kurs Średni (NBP)</th>
                <th className="text-center align-middle text-nowrap">Trend</th>
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
                    <td className="text-center align-middle text-nowrap p-1">
                      {getTrend(item.rate, history[index + 1]?.rate)}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="3" className="text-center">
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
