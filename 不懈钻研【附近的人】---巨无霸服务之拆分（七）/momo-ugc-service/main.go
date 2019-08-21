package main
import (
  "fmt"
  "context"
  "net/http"
  "strconv"
  "github.com/gin-gonic/gin"
  "go.mongodb.org/mongo-driver/bson"
  "go.mongodb.org/mongo-driver/mongo"
  "go.mongodb.org/mongo-driver/mongo/options"
)
type Ugc struct {
  Uid     int    `bson:"uid",json:"uid"`
  Content string `bson:"content",json:"content"`
  CreateTime string `bson:"createTime",json:"createTime"`
}
func main() {
  router   := gin.New()

  ugcGroup := router.Group("ugc")
  {
    // 首页.
    ugcGroup.GET( "index", func( c *gin.Context ){
      token, ok := c.Request.Header["Token"]
      if !ok {
        c.JSON( http.StatusOK, gin.H{
          "code"    : -1,
          "message" : "需要token",
        } )
        return
      }
      if token[0] == "" {
        c.JSON( http.StatusOK, gin.H{
          "code"    : -1,
          "message" : "需要token",
        } )
        return
      }
      lat, _ := strconv.ParseFloat( c.PostForm("lat"), 64 )
      lng, _ := strconv.ParseFloat( c.PostForm("lng"), 64 )
      mongoClientOptions := options.Client().ApplyURI( "mongodb://127.0.0.1" )
      mongoClient, err   := mongo.Connect( context.TODO(), mongoClientOptions )
      if err != nil {
        fmt.Println( "mongo connect err..." )
      }
      ugcCollection := mongoClient.Database("momo").Collection("ugc")
      ctx := context.TODO()
      cursor, _ := ugcCollection.Find( ctx, bson.M{"loc":bson.M{"$near":bson.M{"$geometry":bson.M{"type":"Point","coordinates":[]float64{lng,lat}}}}} )
      var ugcArr []Ugc
      for cursor.Next( ctx ) {
        //fmt.Println( cursor )
        var ugc Ugc
        if err := cursor.Decode( &ugc ); err != nil {
          fmt.Println( err )
        }
        ugcArr = append( ugcArr, ugc )
      }
      c.JSON( http.StatusOK, gin.H{
        "code" : 0,
        "data" : ugcArr,
      } )
    } )
    // 发布.
    ugcGroup.POST( "create", func( c *gin.Context ) {
    } )
  }

  router.Run( ":8000" )
}
