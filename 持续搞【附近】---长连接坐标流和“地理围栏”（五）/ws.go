package main
import (
  "log"
  "fmt"
  "net/http"
  "github.com/gorilla/websocket"
  "encoding/json"
  "context"
  "go.mongodb.org/mongo-driver/bson"
  "go.mongodb.org/mongo-driver/bson/primitive"
  "go.mongodb.org/mongo-driver/mongo"
  "go.mongodb.org/mongo-driver/mongo/options"
)
type coordsStruct struct {
  Lat float64 `json:lat`
  Lng float64 `json:lng`
}
type fenceStruct struct {
  Id primitive.ObjectID "_id,omitempty"
}
// 配置一些websocket的option项
var upgrader = websocket.Upgrader{
  CheckOrigin: func( r *http.Request ) bool {
    return true
  },
}
func main() {
  http.HandleFunc( "/", fence )
  log.Fatal( http.ListenAndServe( ":8000", nil ) )
}

// 地理围栏服务
func fence( w http.ResponseWriter, r *http.Request ) {
  conn, err := upgrader.Upgrade( w, r, nil )
  // 这个地方已经要校验失败，err如果不校验，后面会出错
  if err != nil {
    fmt.Println( "ws upgrade err:", err )
    return
  }
  defer conn.Close()
  // 进入到ws服务无限循环中...
  for {
    messageType, message, _ := conn.ReadMessage()
    // 反序列化json
    var coords coordsStruct
    err := json.Unmarshal( []byte( message ), &coords )
    if err != nil {
      fmt.Println( "json decode err : ", err )
    }

    // 开始处理经纬度是否在多边形中
    fmt.Println( "收到坐标：", string( message ) )
    var fence fenceStruct
    clientOptions := options.Client().ApplyURI( "mongodb://127.0.0.1" ) 
    client, err := mongo.Connect( context.TODO(), clientOptions )
    if err != nil {
      fmt.Println( "mongo connect err..." )
    }
    geoCollection := client.Database("momo").Collection("geo")
    ret := geoCollection.FindOne( context.TODO(), bson.M{"fence":bson.M{"$geoIntersects":bson.M{"$geometry":bson.M{"type":"Point","coordinates":[]float64{coords.Lng,coords.Lat}}}}} )
    if err := ret.Decode( &fence ); err != nil {
      fmt.Println( "Decode err : ", err )
      return
    }

    response, err := json.Marshal( fence )
    if err != nil {
      fmt.Println( "json marshal err : ", err )
      return
    }
    conn.WriteMessage( messageType,	[]byte( response ) )
  }
}
