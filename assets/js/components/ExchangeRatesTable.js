import React from "react"
import { useEffect, useState } from "react"
import { Link } from "react-router-dom"

export const ExchangeRatesTable = () => {
  const [rates, setRates] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch("/api/rates")
      .then((response) => response.json())
      .then((data) => {
        if (data.rates) {
          setRates(data.rates)
        }
        setLoading(false)
      })
      .catch((error) => {
        console.error("Error fetching exchange rates:", error)
        setLoading(false)
      })
  }, [])

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
      <div className="card shadow-sm">
        <div className="card-header bg-primary text-white">
          <h4 className="mb-0">Aktualne Kursy Walut</h4>
        </div>
        <div className="card-body p-0">
          <table className="table table-striped mb-0">
            <thead className="thead-dark">
              <tr>
                <th>Waluta</th>
                <th>Kod</th>
                <th>Kurs Średni (NBP)</th>
                <th>Skup</th>
                <th>Sprzedaż</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {rates.map((rate) => (
                <tr key={rate.currency}>
                  <td>
                    {rate.currency.charAt(0).toUpperCase() +
                      rate.currency.slice(1)}
                  </td>
                  <td>
                    <strong>{rate.code}</strong>
                  </td>
                  <td>{rate.mid_rate} PLN</td>
                  <td
                    className={
                      rate.buy_rate
                        ? "text-success font-weight-bold"
                        : "text-muted"
                    }
                  >
                    {rate.buy_rate ? `${rate.buy_rate} PLN` : "-"}
                  </td>
                  <td className="text-primary font-weight-bold">
                    {rate.sell_rate} PLN
                  </td>
                  <td>
                    <Link
                      to={`/history/${rate.code}`}
                      className="btn btn-sm btn-primary text-white"
                    >
                      Zobacz Historię
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  )
}
