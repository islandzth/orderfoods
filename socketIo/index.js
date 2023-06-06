var express = require('express');
var app = express();
var server = require('http').Server(app);
var io = require('socket.io')(server);

var port = port = 6768;
server.listen(port, () => console.log('Server running in port ' + port));

io.on('connection', function (socket) { //Bắt sự kiện một client kết nối đến server

    // socket.on('all client', function (data) { //lắng nghe event 'all client'
    //     io.sockets.emit('news', data); // gửi cho tất cả client
    // });

    socket.on('broadcast', function (data) { //lắng nghe event 'broadcast'
       // console.log(socket.id);
        socket.broadcast.emit('news', data); // gửi event cho tất cả các client từ client hiện tại
    });

    // socket.on('private', function (data) { //lắng nghe event 'private'
    //     socket.emit('news', ' You send private message: ' + data); // chỉ gửi event cho client hiện tại
    // });

});
