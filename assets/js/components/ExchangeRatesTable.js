import React from "react"
import { useEffect, useState } from "react"
import { Link } from "react-router-dom"
import { Loading } from "./Loading"
import { useSortableData } from "../hooks/useSortableData"

export const ExchangeRatesTable = () => {
  const [rates, setRates] = useState([])
  const [loading, setLoading] = useState(true)

  const {
    items: sortedRates,
    requestSort,
    sortConfig,
  } = useSortableData(rates, {
    key: "code",
    direction: "asc",
  })

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

  const getSortIndicator = (name) => {
    if (!sortConfig || sortConfig.key !== name)
      return <span className="text-muted small ml-1">↕</span>
    return sortConfig.direction === "asc" ? (
      <span className="text-warning ml-1">▲</span>
    ) : (
      <span className="text-warning ml-1">▼</span>
    )
  }

  if (loading) {
    return <Loading />
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
                <th
                  onClick={() => requestSort("currency")}
                  style={{ cursor: "pointer" }}
                >
                  Waluta {getSortIndicator("currency")}
                </th>
                <th
                  onClick={() => requestSort("code")}
                  style={{ cursor: "pointer" }}
                >
                  Kod {getSortIndicator("code")}
                </th>
                <th
                  onClick={() => requestSort("mid_rate")}
                  style={{ cursor: "pointer" }}
                >
                  Kurs Średni {getSortIndicator("mid_rate")}
                </th>
                <th
                  onClick={() => requestSort("buy_rate")}
                  style={{ cursor: "pointer" }}
                >
                  Skup {getSortIndicator("buy_rate")}
                </th>
                <th
                  onClick={() => requestSort("sell_rate")}
                  style={{ cursor: "pointer" }}
                >
                  Sprzedaż {getSortIndicator("sell_rate")}
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {sortedRates.map((rate) => (
                <tr key={rate.code}>
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
                  <td className="text-right">
                    <Link
                      to={`/history/${rate.code}`}
                      className="btn btn-sm btn-outline-primary"
                    >
                      Historia &rarr;
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
