cmake_minimum_required(VERSION 2.8)
project(core_tests)
set(CMAKE_BUILD_TYPE Debug)
set(CMAKE_CXX_STANDARD 11)
file(GLOB_RECURSE SOURCE_FILES FOLLOW_SYMLINKS src/*.cpp)

add_definitions(-DHAVE_CONFIG_H)

link_directories(/home/htf/workspace/swoole/lib)
include_directories(./include ./ /usr/local/php/include /usr/local/php/include/Zend /usr/local/php/include/main /home/htf/workspace/swoole/ /home/htf/workspace/swoole/include BEFORE)

set(EXECUTABLE_OUTPUT_PATH ${PROJECT_BINARY_DIR}/bin)
add_executable(core_tests ${SOURCE_FILES})
target_link_libraries(core_tests gtest gtest_main pthread swoole)