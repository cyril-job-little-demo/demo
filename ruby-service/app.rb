require 'sinatra'
require 'stripe'
require 'json'

set :bind, "0.0.0.0"
port = ENV["PORT"] || "8080"
set :port, port

Stripe.api_key = '%STRIPE_API_KEY%'

post '/charge/create' do
  request.body.rewind
  data = JSON.parse(request.body.read)

  if data.key?("amount") == false || data.key?("description") == false
    status 400
    return;
  end


  Stripe::Charge.create({
    amount: data["amount"],
    description: data["description"],
    currency: 'gbp',
    source: 'tok_visa',
  })

  status 200
end