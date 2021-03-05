package main

import (
	//"fmt"
	"log"
	"net/http"
	"github.com/stripe/stripe-go/v72"
	"github.com/stripe/stripe-go/v72/balancetransaction"
	"encoding/json"
)

type Transaction struct {
	Amount int64
	TransactionDate int64
	Description string
	Fee int64
	Net int64
	Type string
}

func payoutHandler(w http.ResponseWriter, r *http.Request) {

	stripe.Key = "%STRIPE_API_KEY%"

	if r.Method == "GET" {

		params := &stripe.BalanceTransactionListParams{}
		
		params.Filters.AddFilter("limit", "", "10")
		i := balancetransaction.List(params)


		var output []Transaction

		for i.Next() {
			bt := i.BalanceTransaction()

			transaction := Transaction{
				Amount: bt.Amount,
				TransactionDate: bt.Created,
				Description: bt.Description,
				Fee: bt.Fee,
				Net: bt.Net,
				Type: string(bt.Type),
			}

			output = append(output, transaction)

		}

		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(output)
		return
	}
	
	w.WriteHeader(http.StatusNotFound)
}

func main() {
	http.HandleFunc("/balance/transactions", payoutHandler)
	log.Fatal(http.ListenAndServe(":8080", nil))
}