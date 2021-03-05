package controllers

import javax.inject._
import play.api._
import play.api.mvc._
import play.api.libs.ws._
import play.api.http.HttpEntity
import play.api.libs.json._
import scala.concurrent.ExecutionContext.Implicits.global

@Singleton
class HomeController @Inject()(ws: WSClient, val controllerComponents: ControllerComponents) extends BaseController {
  def index = Action.async {
    val request: WSRequest = ws.url("https://api.stripe.com/v1/balance")
    val authenticatedRequest: WSRequest = request.addHttpHeaders(
      "Authorization" -> "Bearer sk_test_%STRIPE_API_KEY%"
    )

    authenticatedRequest.get().map { response =>
      Ok(response.body)
    }
  }
}